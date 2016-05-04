<?php
/**
 * @author jsacha
 * @since 29/04/16 15:27
 */

namespace trivago\Rumi\CouchDB;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use jakubsacha\Rumi\Commands\RunCommand;
use jakubsacha\Rumi\Events;
use jakubsacha\Rumi\PluginInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CouchDbPlugin implements PluginInterface
{
    private $tree = [];

    private $updating;

    public function initialize(
        Application $application,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    )
    {
        if (!getenv("RUMI_COUCHDB"))
        {
            return;
        }

        $eventDispatcher->addListener(Events::RUN_STARTED, function (Events\RunStartedEvent $e) use ($eventDispatcher) {
            if (empty($e->getInput()->getArgument(RunCommand::GIT_COMMIT)))
            {
                return;
            }
            $this->tree['commit'] = $e->getInput()->getArgument(RunCommand::GIT_COMMIT);

            foreach ($e->getRunConfig()->getStages() as $stage => $jobs)
            {
                $this->tree['stages'][$stage] = new \stdClass();
                $this->tree['stages'][$stage]->jobs = [];
                foreach ($jobs as $job => $options) {
                    $this->tree['stages'][$stage]->jobs[$job] = new \stdClass();
                    $this->tree['stages'][$stage]->jobs[$job]->status = 'SHEDULED';
                    $this->tree['stages'][$stage]->jobs[$job]->output = '';
                }
            }

            $this->flush();

            $eventDispatcher->addListener(Events::JOB_STARTED, function (Events\JobStartedEvent $e) {
                $stage = $this->findStage($e->getName());
                $this->tree['stages'][$stage]->jobs[$e->getName()]->status = 'INPROGRESS';

                $this->flush();
            });

            $eventDispatcher->addListener(Events::JOB_FINISHED, function (Events\JobFinishedEvent $e) {
                $stage = $this->findStage($e->getName());
                $this->tree['stages'][$stage]->jobs[$e->getName()]->status = $e->getStatus();
                $this->tree['stages'][$stage]->jobs[$e->getName()]->output = $e->getOutput();

                $this->flush();
            });

            $eventDispatcher->addListener(Events::STAGE_FINISHED, function (Events\StageFinishedEvent $e) {
                if ($e->getStatus() != Events\StageFinishedEvent::STATUS_SUCCESS)
                {
                    $this->cancelAllSheduledJobs();
                }
            });

            $eventDispatcher->addListener(Events::RUN_FINISHED, function (Events\RunFinishedEvent $e) {
                echo "Run finished";

                $this->flush();
            });

        });

    }

    private function findStage($jobName)
    {
        foreach ($this->tree['stages'] as $stageName => $jobs) {
            foreach ($jobs->jobs as $job => $_) {
                if ($job == $jobName) {
                    return $stageName;
                }
            }
        }
        return null;
    }

    private function cancelAllSheduledJobs()
    {
        foreach ($this->tree['stages'] as $stageName => $jobs) {
            foreach ($jobs->jobs as $jobName => $job) {
                if($job->status == 'SHEDULED')
                {
                    $this->tree['stages'][$stageName]->jobs[$jobName]->status = Events\JobFinishedEvent::STATUS_ABORTED;
                }
            }
        }
    }

    private function flush()
    {
        try{

            $request = new Request(
                "PUT",
                'http://'.getenv("RUMI_COUCHDB").'/runs/'.$this->tree['commit'],
                [],
                json_encode($this->tree, JSON_UNESCAPED_UNICODE)
            );
            $client = new Client();
            $response = $client->send($request)->getBody();
            $json = json_decode($response);
            $this->tree['_rev'] = $json->rev;
        }
        catch (\Exception $e)
        {
            
        }

    }
}