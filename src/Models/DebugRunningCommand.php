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


use Symfony\Component\Process\Exception\ProcessTimedOutException;

class DebugRunningCommand implements RunningCommandInterface {
    /**
     * @var RunningCommandInterface
     */
    private $command;

    /**
     * @param RunningCommandInterface $command
     */
    public function __construct(RunningCommandInterface $command) {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommand() {
        return $this->command->getCommand();
    }

    /**
     * @throws ProcessTimedOutException In case the timeout was reached
     */
    public function checkTimeout() {
        return $this->command->checkTimeout();
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool {
        return $this->command->isSuccessful();
    }

    /**
     * @return bool
     */
    public function isFailed(): bool {
        return $this->command->isFailed();
    }

    /**
     * @return float|null
     */
    public function getTimeout() {
        return $this->command->getTimeout();
    }

    /**
     * @return string
     */
    public function getYamlPath(): string {
        return $this->command->getYamlPath();
    }

    public function start() {
        return $this->command->start();
    }

    /**
     * Tears down running process.
     */
    public function tearDown() {
        // in debug mode we want to keep everything there
    }

    /**
     * @return bool
     */
    public function isRunning(): bool {
        return $this->command->isRunning();
    }

    /**
     * @return string
     */
    public function getOutput(): string {
        return $this->command->getOutput();
    }

    /**
     * @return string
     */
    public function getJobName(): string {
        return $this->command->getJobName();
    }
}
