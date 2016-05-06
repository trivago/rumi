<?php
/**
 * @author jsacha
 *
 * @since 29/04/16 15:27
 */

namespace jakubsacha\Rumi\Plugins\CouchDB;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use jakubsacha\Rumi\Commands\RunCommand;
use jakubsacha\Rumi\Events;
use jakubsacha\Rumi\Plugins\CouchDb\Models\Job;
use jakubsacha\Rumi\Plugins\CouchDb\Models\Run;
use jakubsacha\Rumi\Plugins\CouchDb\Models\Stage;
use jakubsacha\Rumi\Plugins\PluginInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CouchDbPlugin implements PluginInterface
{
    const ENV_VARIABLE = 'RUMI_COUCHDB';

    /**
     * @var Run
     */
    private $run;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Uploader
     */
    private $uploader;


    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    ) {
        if (!getenv(self::ENV_VARIABLE)) {
            return;
        }

        $this->output = $output;
        $this->uploader = new Uploader(getenv(self::ENV_VARIABLE), new Client());

        $eventDispatcher->addListener(Events::RUN_STARTED, function (Events\RunStartedEvent $e) use ($eventDispatcher, $input) {
            if (empty($input->getArgument(RunCommand::GIT_COMMIT))) {
                return;
            }
            $this->run = new Run($input->getArgument(RunCommand::GIT_COMMIT));

            foreach ($e->getRunConfig()->getStages() as $stageName => $jobs) {
                $stage = new Stage($stageName);
                foreach ($jobs as $jobName => $options) {
                    $stage->addJob(new Job($jobName, 'SHEDULED'));
                }
                $this->run->addStage($stage);
            }

            $eventDispatcher->addListener(Events::JOB_STARTED, function (Events\JobStartedEvent $e) {
                $stage = $this->findStage($e->getName());
                $stage->getJob($e->getName())->setStatus('INPROGRESS');

                $this->flush();
            });

            $eventDispatcher->addListener(Events::JOB_FINISHED, function (Events\JobFinishedEvent $e) {
                $stage = $this->findStage($e->getName());
                $stage->getJob($e->getName())->setStatus($e->getStatus());
                $stage->getJob($e->getName())->setOutput($e->getOutput());

                $this->flush();
            });

            $eventDispatcher->addListener(Events::STAGE_FINISHED, function (Events\StageFinishedEvent $e) {
                if ($e->getStatus() != Events\StageFinishedEvent::STATUS_SUCCESS) {
                    $this->cancelAllSheduledJobs();
                }
            });

            $eventDispatcher->addListener(Events::RUN_FINISHED, function (Events\RunFinishedEvent $e) {
                $this->flush();
            });

        });
    }

    /**
     * @param $jobName
     * @return Stage|null
     */
    private function findStage($jobName)
    {
        foreach ($this->run->getStages() as $stage) {
            /** @var Stage $stage */
            foreach ($stage->getJobs() as $job) {
                /** @var Job $job */
                if ($job->getName() == $jobName) {
                    return $stage;
                }
            }
        }

        return null;
    }

    private function cancelAllSheduledJobs()
    {
        foreach ($this->run->getStages() as $stage) {
            /** @var Stage $stage */
            foreach ($stage->getJobs() as $job) {
                /** @var Job $job */
                if ($job->getStatus() == 'SHEDULED') {
                    $job->setStatus(Events\JobFinishedEvent::STATUS_ABORTED);
                }
            }
        }
    }

    private function flush()
    {
        try {
            $this->uploader->flush($this->run);
        } catch (\Exception $e) {
            $this->output->writeln('<error>CouchDB plugin error:</error> '.$e->getMessage());
        }
    }
}
