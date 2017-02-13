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

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\RunningProcessFactoryInterface;

/**
 * @covers \Trivago\Rumi\Models\RunningCommand
 */
class RunningCommandTest extends TestCase
{
    /**
     * @var RunningCommand
     */
    private $SUT;

    /**
     * @var JobConfig
     */
    private $jobConfig;

    /**
     * @var RunningProcessFactoryInterface
     */
    private $runningProcessFactory;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispather;

    public function setUp()
    {
        $this->eventDispather = $this->prophesize(EventDispatcherInterface::class);
        $this->jobConfig = $this->prophesize(JobConfig::class);
        $this->runningProcessFactory = $this->prophesize(RunningProcessFactoryInterface::class);
        $this->SUT = new RunningCommand(
            $this->jobConfig->reveal(),
            'path',
            $this->runningProcessFactory->reveal(),
            $this->eventDispather->reveal()
        );
    }

    public function testGivenJobConfig_WhenGetCommandCalled_ThenItReturnsValidCommand()
    {
        $this->jobConfig->getCommandsAsString()->willReturn('test_command');

        $this->assertEquals('test_command', $this->SUT->getCommand());
    }

    public function testGivenJobConfig_WhenGetNameCalled_ThenItReturnsValidName()
    {
        $this->jobConfig->getName()->willReturn('test_command');

        $this->assertEquals('test_command', $this->SUT->getJobName());
    }

    public function testGivenProcessIsRunning_WhenTearDownCalled_ThenItRunsTearDown()
    {
        //given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->run()->shouldBeCalled();
        $process = $process_prophecy->reveal();

        $this->runningProcessFactory->getTearDownProcess('path', Argument::type('string'))->willReturn($process);

        // when
        $this->SUT->tearDown();

        //then
    }

    public function testGivenProcessIsRunning_WhenIsRunningCalled_ThenItReturnsValidStatus()
    {
        // given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->start()->shouldBeCalled();
        $process_prophecy->isRunning()->willReturn(true);
        $process = $process_prophecy->reveal();

        $this->setJobConfigProphecy();

        $this->runningProcessFactory->getJobStartProcess('path', Argument::type('string'), 'ci_image', 1200)->willReturn($process);

        // when
        $this->SUT->start();
        $isRunning = $this->SUT->isRunning();

        // then
        $this->assertTrue($isRunning);
    }

    public function testGivenProcessDone_WhenGetOutputCalled_ThenItReturnsIt()
    {
        // given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->start()->shouldBeCalled();
        $process_prophecy->getOutput()->willReturn('outputerroroutput');

        $process = $process_prophecy->reveal();

        $this->setJobConfigProphecy();

        $this->runningProcessFactory->getJobStartProcess('path', Argument::type('string'), 'ci_image', 1200)->willReturn($process);

        // when
        $this->SUT->start();
        $output = $this->SUT->getOutput();

        // then
        $this->assertEquals('outputerroroutput', $output);
    }

    /**
     *
     */
    private function setJobConfigProphecy()
    {
        $this->jobConfig->getCommandsAsString()->willReturn('echo abc');
        $this->jobConfig->getCiContainer()->willReturn('ci_image');
        $this->jobConfig->getTimeout()->willReturn(1200);
        $this->jobConfig->getName()->willReturn(__METHOD__)->shouldBeCalled();
    }
}
