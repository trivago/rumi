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

namespace Trivago\Rumi\Models;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Events;
use Trivago\Rumi\Process\RunningProcessFactoryInterface;

class RunningCommand
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $yamlPath = '';

    /**
     * @var RunningProcessFactoryInterface
     */
    private $runningProcessesFactory;

    /**
     * @var string|null
     */
    private $tempContainerId;

    /**
     * @var JobConfig
     */
    private $jobConfig;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param JobConfig $jobConfig
     * @param string $yamlPath
     * @param RunningProcessFactoryInterface $factory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        JobConfig $jobConfig,
        $yamlPath,
        RunningProcessFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->jobConfig = $jobConfig;
        $this->yamlPath = $yamlPath;
        $this->runningProcessesFactory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->jobConfig->getCommandsAsString();
    }

    /**
     * @throws ProcessTimedOutException In case the timeout was reached
     */
    public function checkTimeout()
    {
        $this->process->checkTimeout();
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->process->isSuccessful();
    }

    public function isFailed(): bool
    {
        return false === $this->isSuccessful();
    }

    /**
     * @return float|null
     */
    public function getTimeout()
    {
        return $this->process->getTimeout();
    }

    /**
     * @return string
     */
    public function getYamlPath(): string
    {
        return $this->yamlPath;
    }

    /**
     * Generates tmp name for running CI job.
     *
     * @return string
     */
    private function getTmpName(): string
    {
        if (empty($this->tempContainerId)) {
            $this->tempContainerId = 'cirunner-'.md5(uniqid().time().$this->getCommand());
        }

        return $this->tempContainerId;
    }

    public function start()
    {
        $this->eventDispatcher->dispatch(Events::JOB_STARTED, new Events\JobStartedEvent($this->getJobName()));

        $this->process =
            $this->runningProcessesFactory->getJobStartProcess(
                $this->getYamlPath(),
                $this->getTmpName(),
                $this->jobConfig->getCiContainer(),
                $this->jobConfig->getTimeout()
            );

        $this->process->start();
    }

    /**
     * Tears down running process.
     */
    public function tearDown()
    {
        $this
            ->runningProcessesFactory
            ->getTearDownProcess($this->getYamlPath(), $this->getTmpName())
            ->run();
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->process->getOutput();
    }

    /**
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobConfig->getName();
    }
}
