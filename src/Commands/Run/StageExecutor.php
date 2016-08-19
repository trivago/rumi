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
use Trivago\Rumi\Builders\DockerComposeYamlBuilder;
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\JobFinishedEvent;
use Trivago\Rumi\Events\JobStartedEvent;
use Trivago\Rumi\Exceptions\CommandFailedException;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\RunningCommand;
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
        RunningProcessesFactory $runningProcessesFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dockerComposeYamlBuilder = $dockerComposeYamlBuilder;
        $this->runningProcessesFactory = $runningProcessesFactory;
    }

    /**
     * @param JobConfig[] $jobs
     * @param $volume
     * @param OutputInterface  $output
     * @param VCSInfoInterface $VCSInfo
     */
    public function executeStage($jobs, $volume, OutputInterface $output, VCSInfoInterface $VCSInfo)
    {
        $this->handleProcesses($output, $this->startStageProcesses($jobs, $VCSInfo, $volume));
    }

    /**
     * @param JobConfig[]      $jobs
     * @param VCSInfoInterface $VCSInfo
     * @param $volume
     *
     * @return array
     */
    private function startStageProcesses($jobs, VCSInfoInterface $VCSInfo, $volume)
    {
        $processes = [];

        foreach ($jobs as $jobConfig) {
            $runningCommand = new RunningCommand(
                $jobConfig,
                $this->dockerComposeYamlBuilder->build($jobConfig, $VCSInfo, $volume),
                $this->runningProcessesFactory
            );

            $this->eventDispatcher->dispatch(Events::JOB_STARTED, new JobStartedEvent($jobConfig->getName()));

            $runningCommand->start();

            // add random delay to put less stress on the docker daemon
            usleep(rand(100000, 500000));

            $processes[] = $runningCommand;
        }

        return $processes;
    }

    /**
     * @param OutputInterface  $output
     * @param RunningCommand[] $processes
     *
     * @throws \Exception
     */
    private function handleProcesses(OutputInterface $output, $processes)
    {
        try {
            while (count($processes)) {
                foreach ($processes as $id => $runningCommand) {
                    if ($runningCommand->isRunning()) {
                        $runningCommand->getProcess()->checkTimeout();

                        continue;
                    }
                    unset($processes[$id]);

                    $output->writeln(sprintf('<info>Executing job: %s</info>', $runningCommand->getJobName()));
                    $output->write($runningCommand->getOutput());

                    $isSuccessful = $runningCommand->getProcess()->isSuccessful();

                    $this->dispatchJobFinishedEvent(
                        $runningCommand,
                        $isSuccessful ? JobFinishedEvent::STATUS_SUCCESS : JobFinishedEvent::STATUS_FAILED
                    );

                    $runningCommand->tearDown();

                    if ($isSuccessful) {
                        continue;
                    }

                    throw new CommandFailedException($runningCommand->getCommand());
                }
                usleep(500000);
            }
        } catch (CommandFailedException $e) {
            $output->writeln("<error>Command '".$e->getMessage()."' failed</error>");

            $this->tearDownProcesses($output, $processes);

            throw new \Exception('Stage failed', ReturnCodes::FAILED);
        }
    }

    /**
     * @param OutputInterface  $output
     * @param RunningCommand[] $processes
     */
    private function tearDownProcesses(OutputInterface $output, $processes)
    {
        if (empty($processes)) {
            return;
        }

        $output->writeln('Shutting down jobs in background...', OutputInterface::VERBOSITY_VERBOSE);

        foreach ($processes as $runningCommand) {
            $output->writeln('- '.$runningCommand->getCommand(), OutputInterface::VERBOSITY_VERBOSE);

            $this->dispatchJobFinishedEvent($runningCommand, JobFinishedEvent::STATUS_ABORTED);

            $runningCommand->tearDown();
        }
    }

    /**
     * @param RunningCommand $runningCommand
     * @param $status
     */
    private function dispatchJobFinishedEvent(RunningCommand $runningCommand, $status)
    {
        $this->eventDispatcher->dispatch(
            Events::JOB_FINISHED,
            new JobFinishedEvent(
                $status,
                $runningCommand->getJobName(),
                $runningCommand->getOutput()
            )
        );
    }
}
