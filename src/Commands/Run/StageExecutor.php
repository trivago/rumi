<?php

/*
 * Copyright 2016 trivago GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Trivago\Rumi\Commands\Run;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Trivago\Rumi\Builders\DockerComposeYamlBuilder;
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\JobFinishedEvent;
use Trivago\Rumi\Exceptions\CommandFailedException;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\RunningCommand;
use Trivago\Rumi\Models\RunningCommandCollection;
use Trivago\Rumi\Models\StageConfig;
use Trivago\Rumi\Models\VCSInfo\VCSInfoInterface;
use Trivago\Rumi\Process\RunningProcessesFactory;

class StageExecutor
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var DockerComposeYamlBuilder
     */
    private $dockerComposeYamlBuilder;

    /**
     * @var RunningProcessesFactory
     */
    private $runningProcessesFactory;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param DockerComposeYamlBuilder $dockerComposeYamlBuilder
     * @param RunningProcessesFactory  $runningProcessesFactory
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DockerComposeYamlBuilder $dockerComposeYamlBuilder,
        RunningProcessesFactory $runningProcessesFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dockerComposeYamlBuilder = $dockerComposeYamlBuilder;
        $this->runningProcessesFactory = $runningProcessesFactory;
    }

    /**
     * @param StageConfig $stageConfig
     * @param $volume
     * @param OutputInterface $output
     * @param VCSInfoInterface $VCSInfo
     */
    public function executeStage(StageConfig $stageConfig, $volume, OutputInterface $output, VCSInfoInterface $VCSInfo)
    {
        $commandCollection = $this->prepareStageProcesses($stageConfig->getJobs(), $VCSInfo, $volume);

        $this->startStageProcesses($commandCollection);

        $this->handleProcesses($output, $commandCollection);
    }

    /**
     * @param JobConfigCollection $jobConfigCollection
     * @param VCSInfoInterface    $VCSInfo
     * @param $volume
     *
     * @return RunningCommandCollection
     */
    private function prepareStageProcesses(JobConfigCollection $jobConfigCollection, VCSInfoInterface $VCSInfo, $volume)
    {
        $commandsCollection = new RunningCommandCollection();

        /** @var JobConfig $jobConfig */
        foreach ($jobConfigCollection as $jobConfig) {
            $commandsCollection->add(new RunningCommand(
                $jobConfig,
                $this->dockerComposeYamlBuilder->build($jobConfig, $VCSInfo, $volume),
                $this->runningProcessesFactory,
                $this->eventDispatcher
            ));
        }

        return $commandsCollection;
    }

    /**
     * @param OutputInterface  $output
     * @param RunningCommandCollection $commandCollection
     *
     * @throws \Exception
     */
    private function handleProcesses(OutputInterface $output, RunningCommandCollection $commandCollection)
    {
        try {
            while ($commandCollection->getIterator()->count()) {
                /** @var RunningCommand $runningCommand */
                foreach ($commandCollection as $id => $runningCommand) {
                    try {
                        if ($runningCommand->isRunning()) {
                            $runningCommand->checkTimeout();
                            continue;
                        }
                    } catch (ProcessTimedOutException $e) {
                        $timeout = true;
                        // will be handled below
                    }
                    unset($commandCollection[$id]);

                    $output->writeln(sprintf('<info>Executing job: %s</info>', $runningCommand->getJobName()));
                    $output->write($runningCommand->getOutput());
                    if (!empty($timeout)) {
                        $output->writeln(PHP_EOL.'Process timed out after '.$runningCommand->getTimeout().'s');
                    }

                    $this->dispatchJobFinishedEvent($runningCommand);

                    $runningCommand->tearDown();

                    if ($runningCommand->isFailed()) {
                        throw new CommandFailedException($runningCommand->getCommand());
                    }
                }
                usleep(500000);
            }
        } catch (CommandFailedException $e) {
            $output->writeln("<error>Command '".$e->getMessage()."' failed</error>");

            $this->tearDownProcesses($output, $commandCollection);

            throw new \Exception('Stage failed', ReturnCodes::FAILED);
        }
    }

    /**
     * @param OutputInterface  $output
     * @param RunningCommandCollection $runningCommands
     */
    private function tearDownProcesses(OutputInterface $output, RunningCommandCollection $runningCommands)
    {
        if (!$runningCommands->getIterator()->count()) {
            return;
        }

        $output->writeln('Shutting down jobs in background...', OutputInterface::VERBOSITY_VERBOSE);

        foreach ($runningCommands as $runningCommand) {
            $output->writeln('- '.$runningCommand->getCommand(), OutputInterface::VERBOSITY_VERBOSE);

            $this->dispatchJobFinishedEvent($runningCommand, JobFinishedEvent::STATUS_ABORTED);

            $runningCommand->tearDown();

            usleep(500000);
        }
    }

    /**
     * @param RunningCommand $runningCommand
     * @param string|null $status
     */
    private function dispatchJobFinishedEvent(RunningCommand $runningCommand, string $status = null)
    {
        if (null === $status) {
            $status = $runningCommand->isSuccessful()
                ? JobFinishedEvent::STATUS_SUCCESS
                : JobFinishedEvent::STATUS_FAILED;
        }

        $this->eventDispatcher->dispatch(
            Events::JOB_FINISHED,
            new JobFinishedEvent(
                $status,
                $runningCommand->getJobName(),
                $runningCommand->getOutput()
            )
        );
    }

    private function startStageProcesses(RunningCommandCollection $commandsCollection)
    {
        /** @var RunningCommand $runningCommand */
        foreach ($commandsCollection as $runningCommand) {
            $runningCommand->start();

            // add random delay to put less stress on the docker daemon
            usleep(rand(100000, 500000));
        }
    }
}
