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

/**
 * @covers Trivago\Rumi\Models\JobConfig
 */
class JobConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNoCiContainerDefined_WhenGetCiContainerAsStringCalled_ThenFirstContainerIsUsed()
    {
        $job = new JobConfig(
            'name',
            ['www' => [], 'second' => []],
            null,
            null,
            null,
            1200
        );

        $this->assertEquals('www', $job->getCiContainer());
    }

    public function testGivenCiContainerIsDefined_WhenGetCiContainerAsStringCalled_ThenDefinedContainerIsUsed()
    {
        $job = new JobConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            null,
            null,
            1200
        );

        $this->assertEquals('second', $job->getCiContainer());
    }

    public function testGivenParamsArePassed_WhenNewObjectCreated_ThenGettersAreFine()
    {
        $job = new JobConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            'third',
            ['fourth', 'sixth'],
            1200
        );

        $this->assertEquals('name', $job->getName());
        $this->assertEquals('echo "Executing command: fourth" && fourth && echo "Executing command: sixth" && sixth', $job->getCommandsAsString());
        $this->assertEquals(['fourth', 'sixth'], $job->getCommands());
        $this->assertEquals(['www' => [], 'second' => []], $job->getDockerCompose());
        $this->assertEquals('third', $job->getEntryPoint());
    }

    public function testGivenEmptyCommands_WhenNewObjectCreated_ThenGetCommandAsStringReturnsNull()
    {
        $job = new JobConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            'third',
            null,
            1200
        );

        $this->assertEquals('', $job->getCommandsAsString());
    }

    public function testGivenTimeout_WhenNewObjectCreated_ThenGetCTimeouTreturnsValidValue()
    {
        $timeout = 1200;
        $job = new JobConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            'third',
            null,
            $timeout
        );

        $this->assertEquals($timeout, $job->getTimeout());
    }
}
