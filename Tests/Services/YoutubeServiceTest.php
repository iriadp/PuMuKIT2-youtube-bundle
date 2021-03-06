<?php

namespace Pumukit\YoutubeBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pumukit\YoutubeBundle\Services\YoutubeService;
use Pumukit\YoutubeBundle\Document\Youtube;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Tag;
use Pumukit\SchemaBundle\Document\Track;
use Pumukit\SchemaBundle\Services\TagService;
use Pumukit\SchemaBundle\Services\FactoryService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class YoutubeServiceTest extends WebTestCase
{
    private $dm;
    private $youtubeRepo;
    private $mmobjRepo;
    private $tagRepo;
    private $logger;
    private $resourcesDir;
    private $playlistPrivacyStatus;

    public function __construct()
    {
        $options = array('environment' => 'test');
        $kernel = static::createKernel($options);
        $kernel->boot();
        $this->dm = $kernel->getContainer()
          ->get('doctrine_mongodb')->getManager();
        $this->youtubeRepo = $this->dm
          ->getRepository('PumukitYoutubeBundle:Youtube');
        $this->mmobjRepo = $this->dm
          ->getRepository('PumukitSchemaBundle:MultimediaObject');
        $this->tagRepo = $this->dm
          ->getRepository('PumukitSchemaBundle:Tag');
        $this->router = $kernel->getContainer()
          ->get('router');
        $this->logger = $kernel->getContainer()
          ->get('logger');
        $this->factoryService = $kernel->getContainer()
          ->get('pumukitschema.factory');
        $this->notificationSender = $kernel->getContainer()
          ->get('pumukit_notification.sender');
        $this->translator = $kernel->getContainer()
          ->get('translator');
        $this->playlistPrivacyStatus = $kernel->getContainer()
          ->getParameter('pumukit_youtube.playlist_privacy_status');
    }

    public function setUp()
    {
        $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Series')->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:Tag')->remove(array());
        $this->dm->getDocumentCollection('PumukitYoutubeBundle:Youtube')->remove(array());
        $this->dm->flush();
        $this->tagService = new TagService($this->dm);
        $this->youtubeService = new YoutubeService($this->dm, $this->router, $this->tagService, $this->logger, $this->notificationSender, $this->translator, $this->playlistPrivacyStatus);
        $this->resourcesDir = realpath(__DIR__.'/../Resources').'/';
    }

    public function testYoutubeServiceFunctions()
    {
        $this->markTestSkipped('S');

        // Init tags
        $rootTag = $this->createTagWithCode('ROOT', 'ROOT', null, false, true);
        $this->dm->persist($rootTag);
        $this->dm->flush();

        $pubChannelTag = $this->createTagWithCode('PUBCHANNELS', 'PUBCHANNELS', 'ROOT', true, false);
        $youtubeTag = $this->createTagWithCode('YOUTUBE', 'YouTube Playlists', 'ROOT', true, true);
        $this->dm->persist($pubChannelTag);
        $this->dm->persist($youtubeTag);
        $this->dm->flush();

        $youtubeEduTag = $this->createTagWithCode('PUCHYOUTUBE', 'YouTubeEDU', 'PUBCHANNELS', false, true);
        $playlistTag = $this->createTagWithCode('YOUTUBETEST', 'Test Playlist', 'YOUTUBE', false, true);
        $this->dm->persist($youtubeEduTag);
        $this->dm->persist($playlistTag);
        $this->dm->flush();

        // Init Series with MultimediaObject
        $series = $this->factoryService->createSeries();
        $multimediaObject = $this->factoryService->createMultimediaObject($series);

        $multimediaObject = $this->mmobjRepo->find($multimediaObject->getId());
        $addedTags = $this->tagService->addTagToMultimediaObject($multimediaObject, $youtubeEduTag->getId());
        $this->assertTrue($multimediaObject->containsTag($youtubeEduTag));

        // Create Track
        $track = new Track();
        $track->setPath($this->resourcesDir.'camera.mp4');
        $track->addTag('master');
        $track->setDuration(10);
        $this->dm->persist($track);

        $multimediaObject->addTrack($track);
        $this->dm->persist($multimediaObject);
        $this->dm->flush();

        $out = $this->youtubeService->upload($multimediaObject, 27, 'private', false);
        $this->assertEquals(0, $out);

        $playlistTag = $this->tagRepo->findOneByCod('YOUTUBETEST');
        $out2 = $this->youtubeService->moveToList($multimediaObject, $playlistTag->getId(), 27, 'private', false);
        $this->assertEquals(0, $out2);

        $playlist2Tag = $this->createTagWithCode('YOUTUBETEST2', 'Test Playlist 2', 'YOUTUBE', false, true);
        $this->dm->persist($playlist2Tag);
        $this->dm->flush();

        $out3 = $this->youtubeService->moveFromListToList($multimediaObject, $playlist2Tag->getId(), 27, 'private', false);
        $this->assertEquals(0, $out3);

        $out4 = $this->youtubeService->delete($multimediaObject);
        $this->assertEquals(0, $out4);
        $this->assertFalse($multimediaObject->containsTag($youtubeEduTag));

        $out5 = $this->youtubeService->upload($multimediaObject, 27, 'private', false);
        $this->assertEquals(0, $out5);

        $multimediaObject->setTitle("Test auth api");
        $multimediaObject->setDescription("Testing the google auth api to upload videos");
        $multimediaObject->setKeyword("testkeyword");
        $this->dm->persist($multimediaObject);
        $this->dm->flush();

        $out6 = $this->youtubeService->updateMetadata($multimediaObject);
        $this->assertEquals(0, $out6);

        $youtube = $this->youtubeRepo->findOneByMultimediaObjectId($multimediaObject->getId());
        $out7 = $this->youtubeService->updateStatus($youtube);
        $this->assertEquals(0, $out7);

    }

    private function createTagWithCode($code, $title, $tagParentCode = null, $metatag = false, $display = true)
    {
        if ($tag = $this->tagRepo->findOneByCod($code)) {
            throw new \Exception("Nothing done - Tag retrieved from DB id: ".$tag->getId()." cod: ".$tag->getCod());
        }
        $tag = new Tag();
        $tag->setCod($code);
        $tag->setMetatag($metatag);
        $tag->setDisplay($display);
        $tag->setTitle($title, 'es');
        $tag->setTitle($title, 'gl');
        $tag->setTitle($title, 'en');
        if ($tagParentCode){
            if ($parent = $this->tagRepo->findOneByCod($tagParentCode)) {
                $tag->setParent($parent);
            } else {
                throw new \Exception("Nothing done - There is no tag in the database with code ".$tagParentCode." to be the parent tag");
            }
        }
        $this->dm->persist($tag);
        $this->dm->flush();

        return $tag;
    }
}