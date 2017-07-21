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

interface RunningCommandInterface {
    /**
     * @return string
     */
    public function getCommand();

    /**
     * @throws ProcessTimedOutException In case the timeout was reached
     */
    public function checkTimeout();

    /**
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * @return bool
     */
    public function isFailed(): bool;

    /**
     * @return float|null
     */
    public function getTimeout();

    /**
     * @return string
     */
    public function getYamlPath(): string;

    public function start();

    /**
     * Tears down running process.
     */
    public function tearDown();

    /**
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * @return string
     */
    public function getOutput(): string;

    /**
     * @return string
     */
    public function getJobName(): string;
}
