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
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\JobFinishedEvent;
use Trivago\Rumi\Events\JobStartedEvent;
use Trivago\Rumi\Events\RunFinishedEvent;
use Trivago\Rumi\Events\RunStartedEvent;
use Trivago\Rumi\Events\StageFinishedEvent;
use Trivago\Rumi\Events\StageStartedEvent;
use Trivago\Rumi\Process\RunningProcessesFactory;

/**
 * @covers Trivago\Rumi\Commands\RunCommand
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

    public function setUp()
    {
        $this->output = new BufferedOutput();

        vfsStream::setup('directory');

        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('../../config/services.xml');

        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->command = new RunCommand($this->container, $this->eventDispatcher->reveal());
        $this->command->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenNoCiYamlFile_WhenExecuted_ThenDisplaysErrorMessage()
    {
        // given

        // when
        $returnCode = $this->command->run(new ArrayInput([]), $this->output);

        // then
        $this->assertSame("Required file '" . RunCommand::CONFIG_FILE . "' does not exist", trim($this->output->fetch()));
        $this->assertEquals(ReturnCodes::RUMI_YML_DOES_NOT_EXIST, $returnCode);
    }

    public function testGivenCiYamlSyntaxIsWrong_WhenExecuted_ThenDisplaysErrorMessage()
    {
        // given
        file_put_contents(vfsStream::url('directory') . '/' . RunCommand::CONFIG_FILE, 'wrong::' . PHP_EOL . '::yaml_file');

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

        file_put_contents(vfsStream::url('directory') . '/' . RunCommand::CONFIG_FILE, file_get_contents('test/_fixtures/passing-.rumi.yml'));

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
        $startProcess->getErrorOutput()->shouldBeCalled();
        $tearDownProcess = $this->getTearDownProcess();

        /** @var RunningProcessesFactory $processFactory */
        $processFactory = $this->getProcessFactoryMock($startProcess, $tearDownProcess);

        $this->container->set('trivago.rumi.process.running_processes_factory', $processFactory->reveal());

        file_put_contents(vfsStream::url('directory') . '/' . RunCommand::CONFIG_FILE, file_get_contents('test/_fixtures/failing-.rumi.yml'));

        // when
        $returnCode = $this->command->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $commandOutput = $this->output->fetch();

        $this->assertStringStartsWith('Stage: "Stage one"', trim($commandOutput));
        $this->assertContains('failed', $commandOutput);
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

        file_put_contents(vfsStream::url('directory') . '/' . RunCommand::CONFIG_FILE, file_get_contents('test/_fixtures/passing2-.rumi.yml'));

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

        file_put_contents(vfsStream::url('directory') . '/' . RunCommand::CONFIG_FILE, file_get_contents('test/_fixtures/passing-.rumi.yml'));

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

    /**
     * @param $isSuccessful
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
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
