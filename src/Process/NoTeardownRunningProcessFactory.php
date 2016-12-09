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
class NoTeardownRunningProcessFactory extends AbstractRunningProcessFactory
{
    /**
     * @param string $yamlPath
     * @param string $tmpName
     *
     * @return Process
     */
    public function getTearDownProcess(string $yamlPath, string $tmpName): Process
    {
        return new class('echo "skip tear down" > /dev/null') extends Process {
            //@codingStandardsIgnoreStart
            public function run($callback = null) {}
            public function start(callable $callback = null) {}
            //@codingStandardsIgnoreEnd
        };
    }

}
