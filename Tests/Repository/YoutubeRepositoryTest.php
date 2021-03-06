<?php

namespace Pumukit\YoutubeBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pumukit\YoutubeBundle\Document\Youtube;
use Pumukit\SchemaBundle\Document\MultimediaObject;

class YoutubeRepositoryTest extends WebTestCase
{
    private $dm;
    private $repo;

    public function setUp()
    {
        $options = array('environment' => 'test');
        $kernel = static::createKernel($options);
        $kernel->boot();
        $this->dm = $kernel->getContainer()
        ->get('doctrine_mongodb')->getManager();
        $this->repo = $this->dm
        ->getRepository('PumukitYoutubeBundle:Youtube');

        $this->dm->getDocumentCollection('PumukitYoutubeBundle:Youtube')
        ->remove(array());
        $this->dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject')
        ->remove(array());
        $this->dm->flush();
    }

    public function testRepositoryEmpty()
    {
        $this->assertEquals(0, count($this->repo->findAll()));
    }

    public function testRepository()
    {
        $youtube1 = new Youtube();
        $this->dm->persist($youtube1);
        $this->dm->flush();

        $this->assertEquals(1, count($this->repo->findAll()));

        $youtube2 = new Youtube();
        $this->dm->persist($youtube2);
        $this->dm->flush();

        $this->assertEquals(2, count($this->repo->findAll()));
    }

    public function testGetWithAnyStatus()
    {
        $youtube1 = new Youtube();
	$youtube1->setStatus(Youtube::STATUS_ERROR);

        $youtube2 = new Youtube();
	$youtube2->setStatus(Youtube::STATUS_DEFAULT);

        $youtube3 = new Youtube();
	$youtube3->setStatus(Youtube::STATUS_UPLOADING);

        $youtube4 = new Youtube();
	$youtube4->setStatus(Youtube::STATUS_DUPLICATED);

	$this->dm->persist($youtube1);
	$this->dm->persist($youtube2);
	$this->dm->persist($youtube3);
	$this->dm->persist($youtube4);
	$this->dm->flush();

	$youtubes = array($youtube1, $youtube3);
	$statusArray = array(Youtube::STATUS_ERROR, Youtube::STATUS_UPLOADING);
	$results = $this->repo->getWithAnyStatus($statusArray)->toArray();
	$this->assertEquals($youtubes, array_values($results));

	$youtubes = array($youtube2, $youtube3);
	$statusArray = array(Youtube::STATUS_DEFAULT, Youtube::STATUS_UPLOADING);
	$results = $this->repo->getWithAnyStatus($statusArray)->toArray();
	$this->assertEquals($youtubes, array_values($results));

	$youtubes = array($youtube1, $youtube4);
	$statusArray = array(Youtube::STATUS_ERROR, Youtube::STATUS_DUPLICATED);
	$results = $this->repo->getWithAnyStatus($statusArray)->toArray();
	$this->assertEquals($youtubes, array_values($results));
    }

    public function testGetDistinctMultimediaObjectIdsWithAnyStatus()
    {
        $mm1 = new MultimediaObject();
        $mm2 = new MultimediaObject();
        $mm3 = new MultimediaObject();
        $mm4 = new MultimediaObject();

	$this->dm->persist($mm1);
	$this->dm->persist($mm2);
	$this->dm->persist($mm3);
	$this->dm->persist($mm4);
	$this->dm->flush();

        $youtube1 = new Youtube();
	$youtube1->setStatus(Youtube::STATUS_ERROR);
	$youtube1->setMultimediaObjectId($mm1->getId());

        $youtube2 = new Youtube();
	$youtube2->setStatus(Youtube::STATUS_DEFAULT);
	$youtube2->setMultimediaObjectId($mm2->getId());

        $youtube3 = new Youtube();
	$youtube3->setStatus(Youtube::STATUS_UPLOADING);
	$youtube3->setMultimediaObjectId($mm3->getId());

        $youtube4 = new Youtube();
	$youtube4->setStatus(Youtube::STATUS_DUPLICATED);
	$youtube4->setMultimediaObjectId($mm4->getId());

	$this->dm->persist($youtube1);
	$this->dm->persist($youtube2);
	$this->dm->persist($youtube3);
	$this->dm->persist($youtube4);
	$this->dm->flush();

	$mmIds = array($youtube1->getMultimediaObjectId(), $youtube3->getMultimediaObjectId());
	$statusArray = array(Youtube::STATUS_ERROR, Youtube::STATUS_UPLOADING);
	$results = $this->repo->getDistinctMultimediaObjectIdsWithAnyStatus($statusArray)->toArray();
	$this->assertEquals($mmIds, $results);
 
	$mmIds = array($youtube2->getMultimediaObjectId(), $youtube3->getMultimediaObjectId());
	$statusArray = array(Youtube::STATUS_DEFAULT, Youtube::STATUS_UPLOADING);
	$results = $this->repo->getDistinctMultimediaObjectIdsWithAnyStatus($statusArray)->toArray();
	$this->assertEquals($mmIds, $results);

	$mmIds = array($youtube1->getMultimediaObjectId(), $youtube4->getMultimediaObjectId());
	$statusArray = array(Youtube::STATUS_ERROR, Youtube::STATUS_DUPLICATED);
	$results = $this->repo->getDistinctMultimediaObjectIdsWithAnyStatus($statusArray)->toArray();
	$this->assertEquals($mmIds, $results);
    }

    public function testGetWithStatusAndForce()
    {
        $youtube1 = new Youtube();
	$youtube1->setStatus(Youtube::STATUS_ERROR);
	$youtube1->setForce(true);

        $youtube2 = new Youtube();
	$youtube2->setStatus(Youtube::STATUS_DEFAULT);
	$youtube2->setForce(false);

        $youtube3 = new Youtube();
	$youtube3->setStatus(Youtube::STATUS_ERROR);
	$youtube3->setForce(false);

        $youtube4 = new Youtube();
	$youtube4->setStatus(Youtube::STATUS_DEFAULT);
	$youtube4->setForce(true);

        $youtube5 = new Youtube();
	$youtube5->setStatus(Youtube::STATUS_ERROR);
	$youtube5->setForce(false);

	$this->dm->persist($youtube1);
	$this->dm->persist($youtube2);
	$this->dm->persist($youtube3);
	$this->dm->persist($youtube4);
	$this->dm->persist($youtube5);
	$this->dm->flush();

	$youtubes = array($youtube1);
	$status = Youtube::STATUS_ERROR;
	$results = $this->repo->getWithStatusAndForce($status, true)->toArray();
	$this->assertEquals($youtubes, array_values($results));

	$youtubes = array($youtube4);
	$status = Youtube::STATUS_DEFAULT;
	$results = $this->repo->getWithStatusAndForce($status, true)->toArray();
	$this->assertEquals($youtubes, array_values($results));

	$youtubes = array($youtube2);
	$status = Youtube::STATUS_DEFAULT;
	$results = $this->repo->getWithStatusAndForce($status, false)->toArray();
	$this->assertEquals($youtubes, array_values($results));

	$youtubes = array($youtube3, $youtube5);
	$status = Youtube::STATUS_ERROR;
	$results = $this->repo->getWithStatusAndForce($status, false)->toArray();
	$this->assertEquals($youtubes, array_values($results));
    }

    public function testGetDistinctMultimediaObjectIdsWithStatusAndForce()
    {
        $mm1 = new MultimediaObject();
        $mm2 = new MultimediaObject();
        $mm3 = new MultimediaObject();
        $mm4 = new MultimediaObject();

	$this->dm->persist($mm1);
	$this->dm->persist($mm2);
	$this->dm->persist($mm3);
	$this->dm->persist($mm4);
	$this->dm->flush();

        $youtube1 = new Youtube();
	$youtube1->setStatus(Youtube::STATUS_ERROR);
	$youtube1->setForce(true);
	$youtube1->setMultimediaObjectId($mm1->getId());

        $youtube2 = new Youtube();
	$youtube2->setStatus(Youtube::STATUS_DEFAULT);
	$youtube2->setForce(false);
	$youtube2->setMultimediaObjectId($mm2->getId());

        $youtube3 = new Youtube();
	$youtube3->setStatus(Youtube::STATUS_ERROR);
	$youtube3->setForce(false);
	$youtube3->setMultimediaObjectId($mm3->getId());

        $youtube4 = new Youtube();
	$youtube4->setStatus(Youtube::STATUS_DEFAULT);
	$youtube4->setForce(true);
	$youtube4->setMultimediaObjectId($mm4->getId());

        $youtube5 = new Youtube();
	$youtube5->setStatus(Youtube::STATUS_ERROR);
	$youtube5->setForce(false);
	$youtube5->setMultimediaObjectId($mm3->getId());

	$this->dm->persist($youtube1);
	$this->dm->persist($youtube2);
	$this->dm->persist($youtube3);
	$this->dm->persist($youtube4);
	$this->dm->persist($youtube5);
	$this->dm->flush();

	$mmIds = array($mm1->getId());
	$status = Youtube::STATUS_ERROR;
	$results = $this->repo->getDistinctMultimediaObjectIdsWithStatusAndForce($status, true)->toArray();
	$this->assertEquals($mmIds, $results);

	$mmIds = array($mm2->getId());
	$status = Youtube::STATUS_DEFAULT;
	$results = $this->repo->getDistinctMultimediaObjectIdsWithStatusAndForce($status, false)->toArray();
	$this->assertEquals($mmIds, $results);

	$mmIds = array($mm4->getId());
	$status = Youtube::STATUS_DEFAULT;
	$results = $this->repo->getDistinctMultimediaObjectIdsWithStatusAndForce($status, true)->toArray();
	$this->assertEquals($mmIds, $results);

	$mmIds = array($mm3->getId());
	$status = Youtube::STATUS_ERROR;
	$results = $this->repo->getDistinctMultimediaObjectIdsWithStatusAndForce($status, false)->toArray();
	$this->assertEquals($mmIds, $results);
    }

    public function testGetDistinctFieldWithStatusAndForce()
    {
        $link1 = 'https://www.youtube.com/watch?v=my6bfA14vMQ';
        $link2 = 'https://www.youtube.com/watch?v=v6yiPnzHCEA';

        $youtube1 = new Youtube();
	$youtube1->setStatus(Youtube::STATUS_ERROR);
	$youtube1->setForce(true);
	$youtube1->setLink($link1);

        $youtube2 = new Youtube();
	$youtube2->setStatus(Youtube::STATUS_DEFAULT);
	$youtube2->setForce(false);
	$youtube2->setLink($link1);

        $youtube3 = new Youtube();
	$youtube3->setStatus(Youtube::STATUS_ERROR);
	$youtube3->setForce(false);
	$youtube3->setLink($link2);

        $youtube4 = new Youtube();
	$youtube4->setStatus(Youtube::STATUS_DEFAULT);
	$youtube4->setForce(true);
	$youtube4->setLink($link2);

        $youtube5 = new Youtube();
	$youtube5->setStatus(Youtube::STATUS_ERROR);
	$youtube5->setForce(false);
	$youtube5->setLink($link1);

	$this->dm->persist($youtube1);
	$this->dm->persist($youtube2);
	$this->dm->persist($youtube3);
	$this->dm->persist($youtube4);
	$this->dm->persist($youtube5);
	$this->dm->flush();

	$links = array($link1);
	$status = Youtube::STATUS_ERROR;
	$results = $this->repo->getDistinctFieldWithStatusAndForce('link', $status, true)->toArray();
	$this->assertEquals($links, $results);

	$links = array($link1);
	$status = Youtube::STATUS_DEFAULT;
	$results = $this->repo->getDistinctFieldWithStatusAndForce('link', $status, false)->toArray();
	$this->assertEquals($links, $results);

	$links = array($link2);
	$status = Youtube::STATUS_DEFAULT;
	$results = $this->repo->getDistinctFieldWithStatusAndForce('link', $status, true)->toArray();
	$this->assertEquals($links, $results);

	$links = array($link2, $link1);
	$status = Youtube::STATUS_ERROR;
	$results = $this->repo->getDistinctFieldWithStatusAndForce('link', $status, false)->toArray();
	$this->assertEquals($links, $results);
    }

    public function testGetNotMetadataUpdated()
    {
        $youtube1 = new Youtube();
        $youtube1->setMultimediaObjectUpdateDate('2015-08-15 04:09');
        $youtube1->setSyncMetadataDate('2015-08-14 04:15');

        $youtube2 = new Youtube();
        $youtube2->setMultimediaObjectUpdateDate('2015-08-12 04:09');
        $youtube2->setSyncMetadataDate('2015-08-14 04:15');

        $youtube3 = new Youtube();
        $youtube3->setMultimediaObjectUpdateDate('2015-08-16 04:09');
        $youtube3->setSyncMetadataDate('2015-08-14 04:15');

        $youtube4 = new Youtube();
        $youtube4->setMultimediaObjectUpdateDate('2015-08-10 04:09');
        $youtube4->setSyncMetadataDate('2015-08-14 04:15');

        $this->dm->persist($youtube1);
        $this->dm->persist($youtube2);
        $this->dm->persist($youtube3);
        $this->dm->persist($youtube4);
        $this->dm->flush();

        $youtubes = $this->repo->getNotMetadataUpdated();
        $youtubesArray = $youtubes->toArray();
        $this->assertTrue(in_array($youtube1, $youtubesArray));
        $this->assertFalse(in_array($youtube2, $youtubesArray));
        $this->assertTrue(in_array($youtube3, $youtubesArray));
        $this->assertFalse(in_array($youtube4, $youtubesArray));
    }

    public function testGetDistinctIdsNotMetadataUpdated()
    {
        $youtube1 = new Youtube();
        $youtube1->setMultimediaObjectUpdateDate('2015-08-15 04:09');
        $youtube1->setSyncMetadataDate('2015-08-14 04:15');

        $youtube2 = new Youtube();
        $youtube2->setMultimediaObjectUpdateDate('2015-08-12 04:09');
        $youtube2->setSyncMetadataDate('2015-08-14 04:15');

        $youtube3 = new Youtube();
        $youtube3->setMultimediaObjectUpdateDate('2015-08-16 04:09');
        $youtube3->setSyncMetadataDate('2015-08-14 04:15');

        $youtube4 = new Youtube();
        $youtube4->setMultimediaObjectUpdateDate('2015-08-20 04:09');
        $youtube4->setSyncMetadataDate('2015-08-14 04:15');

        $this->dm->persist($youtube1);
        $this->dm->persist($youtube2);
        $this->dm->persist($youtube3);
        $this->dm->persist($youtube4);
        $this->dm->flush();

        $youtubeIds = $this->repo->getDistinctIdsNotMetadataUpdated();
        $youtubeIdsArray = $youtubeIds->toArray();
        $this->assertTrue(in_array($youtube1->getId(), $youtubeIdsArray));
        $this->assertFalse(in_array($youtube2->getId(), $youtubeIdsArray));
        $this->assertTrue(in_array($youtube3->getId(), $youtubeIdsArray));
        $this->assertTrue(in_array($youtube4->getId(), $youtubeIdsArray));
    }
}