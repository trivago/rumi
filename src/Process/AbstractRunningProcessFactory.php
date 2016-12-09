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

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;

/**
 * @package Trivago\Rumi\Process
 * @author Dejan Spasic <spasic.dejan@yahoo.de>
 */
abstract class AbstractRunningProcessFactory implements RunningProcessFactoryInterface
{
    /**
     * Flag to mark if the tear down process should be skipped or not
     *
     * @var bool
     */
    private static $skipTearDown = false;

    /**
     * @return RunningProcessFactoryInterface
     */
    public static function createFactory(): RunningProcessFactoryInterface
    {
        return static::$skipTearDown
            ? new NoTeardownRunningProcessFactory()
            : new RunningProcessFactory();
    }

    /**
     * @param string $yamlPath
     * @param string $tmpName
     * @param string $ciImage
     * @param int $timeout
     * @return Process
     */
    public function getJobStartProcess(string $yamlPath, string $tmpName, string $ciImage, int $timeout): Process
    {
        $process = new Process(
            'docker-compose -f ' . $yamlPath . ' run --name ' . $tmpName . ' ' . $ciImage . ' 2>&1'
        );
        $process->setTimeout($timeout)->setIdleTimeout($timeout);

        return $process;
    }

    /**
     * @param string $yamlPath
     * @param string $tmpName
     *
     * @return Process
     */
    abstract public function getTearDownProcess(string $yamlPath, string $tmpName): Process;

    /**
     * Set flag to skip the tear down process
     */
    public static function disableTearDown()
    {
        static::$skipTearDown = true;
    }

    /**
     * Set flag to run the tear down process
     */
    public static function enableTearDown()
    {
        static::$skipTearDown = false;
    }
}
