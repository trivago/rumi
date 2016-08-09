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

namespace Trivago\Rumi\Commands;

use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\JobFinishedEvent;
use Trivago\Rumi\Events\JobStartedEvent;
use Trivago\Rumi\Events\RunFinishedEvent;
use Trivago\Rumi\Events\RunStartedEvent;
use Trivago\Rumi\Events\StageFinishedEvent;
use Trivago\Rumi\Events\StageStartedEvent;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Process\RunningProcessesFactory;
use Trivago\Rumi\Services\ConfigReader;

/**
 * @covers \Trivago\Rumi\Commands\RunCommand
 * @covers \Trivago\Rumi\Commands\Run\StageExecutor
 */
class RunCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var RunCommand
     */
    private $command;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ConfigReader
     */
    private $configReader;

    public function setUp()
    {
        $this->output = new BufferedOutput();

        vfsStream::setup('directory');

        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('../../src/Resources/config/services.xml');

        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher = $this->eventDispatcher->reveal();

        $this->configReader = $this->prophesize(ConfigReader::class);
        $this->container->set('trivago.rumi.services.config_reader', $this->configReader->reveal());

        $this->container->set('trivago.rumi.event_dispatcher', $eventDispatcher);
        $this->command = new RunCommand($this->container, $eventDispatcher);
        $this->command->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenNoCiYamlFile_WhenExecuted_ThenDisplaysErrorMessage()
    {
        // given
        $this->configReader->getConfig(Argument::any(), Argument::is(CommandAbstract::DEFAULT_CONFIG))->willThrow(new \Exception(
            'Required file \'' . CommandAbstract::DEFAULT_CONFIG . '\' does not exist',
            ReturnCodes::RUMI_YML_DOES_NOT_EXIST
        ));

        // when
        $returnCode = $this->command->run(new ArrayInput([]), $this->output);

        // then
        $this->assertSame("Required file '" . CommandAbstract::DEFAULT_CONFIG . "' does not exist", trim($this->output->fetch()));
        $this->assertEquals(ReturnCodes::RUMI_YML_DOES_NOT_EXIST, $returnCode);
    }

    public function testGivenCiYamlSyntaxIsWrong_WhenExecuted_ThenDisplaysErrorMessage()
    {
        // given
        $this->configReader->getConfig(Argument::any(), Argument::is(CommandAbstract::DEFAULT_CONFIG))->willThrow(new ParseException(
            'Unable to parse at line 2 (near "::yaml_file").'
        ));

        // when
        $returnCode = $this->command->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $this->assertSame('Unable to parse at line 2 (near "::yaml_file").', trim($this->output->fetch()));
        $this->assertEquals(ReturnCodes::FAILED, $returnCode);
    }

    public function testGivenValidCiYamlAndBuildIsOk_WhenExecuted_ThenDisplaysConfirmationMessage()
    {
        // given
        $startProcess = $this->getStartProcess(true);
        $tearDownProcess = $this->getTearDownProcess();

        $processFactory = $this->getProcessFactoryMock($startProcess, $tearDownProcess);

        $this->container->set('trivago.rumi.process.running_processes_factory', $processFactory->reveal());

        $this->configReader->getConfig(Argument::any(), Argument::is(CommandAbstract::DEFAULT_CONFIG))
            ->willReturn(
                new RunConfig(['Stage one' => ['Job one' => ['docker' => ['www' => ['image' => 'abc']]]]], [], null)
            );

        // when
        $returnCode = $this->command->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $commandOutput = $this->output->fetch();

        $this->assertStringStartsWith('Stage: "Stage one"', trim($commandOutput));
        $this->assertContains('Build successful', $commandOutput);
        $this->assertEquals(0, $returnCode);
    }

    public function testGivenValidCiYamlAndBuildFails_WhenExecuted_ThenDisplaysConfirmationMessage()
    {
        // given
        $startProcess = $this->getStartProcess(false);
        $startProcess->isRunning()->willReturn(true, false);
        $startProcess->checkTimeout()->willReturn(null);
        $errorOutput = '##error output##';

        $startProcess->getErrorOutput()->willReturn($errorOutput)->shouldBeCalled();
        $tearDownProcess = $this->getTearDownProcess();

        /** @var RunningProcessesFactory $processFactory */
        $processFactory = $this->getProcessFactoryMock($startProcess, $tearDownProcess);

        $this->container->set('trivago.rumi.process.running_processes_factory', $processFactory->reveal());

        $this->configReader->getConfig(Argument::any(), Argument::is(CommandAbstract::DEFAULT_CONFIG))
            ->willReturn(
                new RunConfig(['Stage one' => ['Job one' => ['docker' => ['www' => ['image' => 'abc']]]]], [], null)
            );

        // when
        $returnCode = $this->command->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $commandOutput = $this->output->fetch();

        $this->assertStringStartsWith('Stage: "Stage one"', trim($commandOutput));
        $this->assertContains($errorOutput, $commandOutput);
        $this->assertNotContains($errorOutput.$errorOutput, $commandOutput);
        $this->assertEquals(ReturnCodes::FAILED, $returnCode);
    }

    /**
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    public function testGivenJobsAreSuccessful_WhenRunIsStarted_ThenEventsAreTriggeredWithProperStatuses()
    {
        // given
        $oStartProcess = $this->getStartProcess(true);
        $oTearDownProcess = $this->getTearDownProcess();

        $oProcessFactory = $this->getProcessFactoryMock($oStartProcess, $oTearDownProcess);

        $this->container->set('trivago.rumi.process.running_processes_factory', $oProcessFactory->reveal());

        $this->configReader->getConfig(Argument::any(), Argument::is(CommandAbstract::DEFAULT_CONFIG))
            ->willReturn(
                new RunConfig([
                    'Stage one' => [
                        'Job one' => ['docker' => ['www' => ['image' => 'abc']]],
                        'Job two' => ['docker' => ['www' => ['image' => 'abc']]],
                    ],
                    'Stage two' => [
                        'Job one' => ['docker' => ['www' => ['image' => 'abc']]],
                        'Job two' => ['docker' => ['www' => ['image' => 'abc']]],
                    ],
                ], [], null)
            );

        // when
        $this->command->run(
            new ArrayInput(['volume' => '.']), $this->output
        );

        // then
        $this
            ->eventDispatcher
            ->dispatch(Events::RUN_STARTED, Argument::type(RunStartedEvent::class))
            ->shouldBeCalledTimes(1);

        $this
            ->eventDispatcher
            ->dispatch(Events::STAGE_STARTED, Argument::type(StageStartedEvent::class))
            ->shouldBeCalledTimes(2);

        $this
            ->eventDispatcher
            ->dispatch(Events::JOB_STARTED, Argument::type(JobStartedEvent::class))
            ->shouldBeCalledTimes(4);

        $this
            ->eventDispatcher
            ->dispatch(Events::JOB_FINISHED, Argument::that(function (JobFinishedEvent $e) {
                return $e->getStatus() == JobFinishedEvent::STATUS_SUCCESS;
            }))
            ->shouldBeCalledTimes(4);

        $this
            ->eventDispatcher
            ->dispatch(Events::STAGE_FINISHED, Argument::that(function (StageFinishedEvent $e) {
                return $e->getStatus() == StageFinishedEvent::STATUS_SUCCESS;
            }))
            ->shouldBeCalledTimes(2);

        $this
            ->eventDispatcher
            ->dispatch(Events::RUN_FINISHED, Argument::that(function (RunFinishedEvent $e) {
                return $e->getStatus() == RunFinishedEvent::STATUS_SUCCESS;
            }))
            ->shouldBeCalledTimes(1);
    }

    /**
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    public function testGivenJobFails_WhenRunIsStarted_ThenEventsAreTriggeredWithProperStatuses()
    {
        // given
        $oStartProcess = $this->getStartProcess(false);
        $oTearDownProcess = $this->getTearDownProcess();

        $oProcessFactory = $this->getProcessFactoryMock($oStartProcess, $oTearDownProcess);

        $this->container->set('trivago.rumi.process.running_processes_factory', $oProcessFactory->reveal());

        $this->configReader->getConfig(Argument::any(), Argument::is(CommandAbstract::DEFAULT_CONFIG))
            ->willReturn(
                new RunConfig(['Stage one' => ['Job one' => ['docker' => ['www' => ['image' => 'abc']]]]], [], null)
            );

        // when
        $this->command->run(
            new ArrayInput(['volume' => '.']), $this->output
        );

        // then
        $this
            ->eventDispatcher
            ->dispatch(Events::RUN_STARTED, Argument::type(RunStartedEvent::class))
            ->shouldBeCalledTimes(1);

        $this
            ->eventDispatcher
            ->dispatch(Events::STAGE_STARTED, Argument::type(StageStartedEvent::class))
            ->shouldBeCalledTimes(1);

        $this
            ->eventDispatcher
            ->dispatch(Events::JOB_STARTED, Argument::type(JobStartedEvent::class))
            ->shouldBeCalledTimes(1);

        $this
            ->eventDispatcher
            ->dispatch(Events::JOB_FINISHED, Argument::that(function (JobFinishedEvent $e) {
                return $e->getStatus() == JobFinishedEvent::STATUS_FAILED;
            }))
            ->shouldBeCalledTimes(1);

        $this
            ->eventDispatcher
            ->dispatch(Events::STAGE_FINISHED, Argument::that(function (StageFinishedEvent $e) {
                return $e->getStatus() == StageFinishedEvent::STATUS_FAILED;
            }))
            ->shouldBeCalledTimes(1);

        $this
            ->eventDispatcher
            ->dispatch(Events::RUN_FINISHED, Argument::that(function (RunFinishedEvent $e) {
                return $e->getStatus() == RunFinishedEvent::STATUS_FAILED;
            }))
            ->shouldBeCalledTimes(1);
    }

    public function testGivenDifferentCiYamlFileName_WhenExecuted_ThenLookForThatFileInstead()
    {
        // given
        $configFile = '../rumi-dev.yml';
        $exceptionMessage = 'Required file \'' . $configFile . '\' does not exist';
        $input = new ArrayInput([ '--config' => $configFile ]);

        $this->configReader
            ->getConfig(Argument::any(), Argument::is($configFile))
            ->willThrow(new \Exception($exceptionMessage, ReturnCodes::RUMI_YML_DOES_NOT_EXIST))
            ->shouldBeCalledTimes(1);

        // when
        $returnCode = $this->command->run($input, $this->output);

        // then
        $this->assertNotEquals(CommandAbstract::DEFAULT_CONFIG, $configFile);
        $this->assertSame("Required file '" . $configFile . "' does not exist", trim($this->output->fetch()));
        $this->assertEquals(ReturnCodes::RUMI_YML_DOES_NOT_EXIST, $returnCode);
    }

    /**
     * @param $isSuccessful
     *
     * @return \Prophecy\Prophecy\ObjectProphecy|Process
     */
    protected function getStartProcess($isSuccessful)
    {
        $startProcess = $this->prophesize(Process::class);
        $startProcess->start()->shouldBeCalled();
        $startProcess->isRunning()->shouldBeCalled();
        $startProcess->isSuccessful()->willReturn($isSuccessful)->shouldBeCalled();
        $startProcess->getOutput()->shouldBeCalled();
        $startProcess->getErrorOutput()->shouldBeCalled();

        return $startProcess;
    }

    /**
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function getTearDownProcess()
    {
        $tearDownProcess = $this->prophesize(Process::class);
        $tearDownProcess->run()->shouldBeCalled();

        return $tearDownProcess;
    }

    /**
     * @param $startProcess
     * @param $tearDownProcess
     *
     * @return RunningProcessesFactory
     */
    protected function getProcessFactoryMock($startProcess, $tearDownProcess)
    {
        /** @var RunningProcessesFactory $processFactory */
        $processFactory = $this->prophesize(RunningProcessesFactory::class);

        $processFactory->getJobStartProcess(Argument::any(), Argument::any(), Argument::any())
            ->willReturn($startProcess->reveal());

        $processFactory->getTearDownProcess(Argument::any(), Argument::any())
            ->willReturn($tearDownProcess->reveal());

        return $processFactory;
    }
}
