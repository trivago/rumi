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

/**
 * @covers Trivago\Rumi\Process\RunningProcessesFactory
 */
class RunningProcessesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RunningProcessesFactory
     */
    private $SUT;

    public function setUp()
    {
        $this->SUT = new RunningProcessesFactory();
    }

    public function testGetJobStartProcess()
    {
        $process = $this->SUT->getJobStartProcess(
            'a', 'b', 'c'
        );
        $this->assertEquals('docker-compose -f a run --name b c', $process->getCommandLine());
    }

    public function testGetTearDownProcess()
    {
        $process = $this->SUT->getTearDownProcess(
            'a', 'b'
        );
        $this->assertEquals('docker rm -f b;
            docker-compose -f a rm -v --force;
            docker rm -f $(docker-compose -f a ps -q)', $process->getCommandLine());
    }
}
