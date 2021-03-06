<?php

namespace Pumukit\YoutubeBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Pumukit\SchemaBundle\Document\Tag;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\YoutubeBundle\Document\Youtube;
use Psr\Log\LoggerInterface;

class YoutubeUpdateStatusCommand extends ContainerAwareCommand
{
    private $dm = null;
    private $tagRepo = null;
    private $mmobjRepo = null;
    private $youtubeRepo = null;
    private $broadcastRepo = null;

    private $youtubeService;

    private $okUpdates = array();
    private $failedUpdates = array();
    private $errors = array();

    private $logger;

    protected function configure()
    {
        $this
            ->setName('youtube:update:status')
            ->setDescription('Update Youtube status of the video')
            ->setHelp(<<<EOT
Command to upload a controlled videos to Youtube.

EOT
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initParameters();

        $statusArray = array(Youtube::STATUS_REMOVED, Youtube::STATUS_NOTIFIED_ERROR);
        $youtubes = $this->youtubeRepo->getWithoutAnyStatus($statusArray);

        $this->updateVideoStatusInYoutube($youtubes, $output);
        $this->checkResultsAndSendEmail();
    }

    private function initParameters()
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $this->tagRepo = $this->dm->getRepository("PumukitSchemaBundle:Tag");
        $this->mmobjRepo = $this->dm->getRepository("PumukitSchemaBundle:MultimediaObject");
        $this->youtubeRepo = $this->dm->getRepository("PumukitYoutubeBundle:Youtube");
        $this->broadcastRepo = $this->dm->getRepository("PumukitSchemaBundle:Broadcast");

        $this->youtubeService = $this->getContainer()->get('pumukityoutube.youtube');

        $this->okUpdates = array();
        $this->failedUpdates = array();
        $this->errors = array();

        $this->logger = $this->getContainer()->get('monolog.logger.youtube');
    }

    private function updateVideoStatusInYoutube($youtubes, OutputInterface $output)
    {
        foreach ($youtubes as $youtube) {
            $multimediaObject = $this->findByYoutubeIdAndPumukit1Id($youtube, false);
            if ($multimediaObject == null) continue;
            try {
                $infoLog = __CLASS__.' ['.__FUNCTION__
                  .'] Started updating Youtube status video "'.$youtube->getId().'"';
                $this->logger->addInfo($infoLog);
                $output->writeln($infoLog);
                $outUpdate = $this->youtubeService->updateStatus($youtube);
                if (0 !== $outUpdate) {
                    $errorLog = __CLASS__.' ['.__FUNCTION__
                      .'] Unknown error on the update in Youtube status video "'
                      .$youtube->getId().'": ' . $outUpdate;
                    $this->logger->addError($errorLog);
                    $output->writeln($errorLog);
                    $this->errors[] = $errorLog;
                    continue;
                }
                if ($multimediaObject) $this->okUpdates[] = $multimediaObject;
            } catch (\Exception $e) {
                $errorLog = __CLASS__.' ['.__FUNCTION__
                  .'] The update of the Youtube status video "'.$youtube->getId()
                  .'" failed: '.$e->getMessage();
                $this->logger->addError($errorLog);
                $output->writeln($errorLog);
                if ($multimediaObject) $this->failedUpdates[] = $multimediaObject;
                $this->errors[] = substr($e->getMessage(), 0, 100);
            }
        }
    }

    private function checkResultsAndSendEmail()
    {
        if (!empty($this->errors)) {
            $this->youtubeService->sendEmail('status update', $this->okUpdates, $this->failedUpdates, $this->errors);
        }
    }

    private function findByYoutubeIdAndPumukit1Id(Youtube $youtube, $pumukit1Id=false)
    {
        return $this->mmobjRepo->createQueryBuilder()
            ->field('properties.youtube')->equals($youtube->getId())
            ->field('properties.pumukit1id')->exists($pumukit1Id)
            ->getQuery()
            ->getSingleResult();
    }
}
