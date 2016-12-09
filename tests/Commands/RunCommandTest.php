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

use Prophecy\Argument;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\RunFinishedEvent;
use Trivago\Rumi\Events\RunStartedEvent;
use Trivago\Rumi\RumiApplication;

/**
 * @covers \Trivago\Rumi\Commands\RunCommand
 * @covers \Trivago\Rumi\Commands\Run\StageExecutor
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class RunCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RumiApplication
     */
    private $app;

    protected function setUp()
    {
        $this->app = new RumiApplication();
        $this->app->setAutoExit(false);
    }

    public function testGivenNoCiYamlFile_WhenExecuted_ThenDisplaysErrorMessage()
    {
        $output = new BufferedOutput();
        $rumiFile = 'IHaveNoConfig4U';

        $exitCode = $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );

        $this->assertContains(
            sprintf("Required file '%s' does not exist", $rumiFile),
            $output->fetch()
        );
        $this->assertExitCode(ReturnCodes::RUMI_YML_DOES_NOT_EXIST, $exitCode);
    }

    public function testGivenCiYamlSyntaxIsWrong_WhenExecuted_ThenDisplaysErrorMessage()
    {
        $output = new BufferedOutput();
        $rumiFile = 'tests/Commands/Fixtures/rumiConfigWithSyntaxError.yml';

        $exitCode = $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );

        $this->assertContains('Unable to parse at line 1 (near "-;:23@@@@")', $output->fetch());
        $this->assertExitCode(ReturnCodes::FAILED, $exitCode);
    }

    public function testGivenCiYamlAbsolutePath_WhenExecuted_ThenConfigFileShouldBeFound()
    {
        $output = new BufferedOutput();
        $rumiFile = __DIR__ . '/Fixtures/rumiConfigWithSyntaxError.yml';

        $exitCode = $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );

        $this->assertContains('Unable to parse at line 1 (near "-;:23@@@@")', $output->fetch());
        $this->assertExitCode(ReturnCodes::FAILED, $exitCode);
    }

    public function testGivenValidCiYamlAndBuildIsOk_WhenExecuted_ThenDisplaysConfirmationMessage()
    {
        $output = new BufferedOutput();
        $rumiFile = 'tests/Commands/Fixtures/rumiConfigBuildIsOkay.yml';

        $exitCode = $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );

        $bufferedOutput = $output->fetch();

        $this->assertContains('Build successful', $bufferedOutput);
        $this->assertContains('Stage: "Stage one"', $bufferedOutput);
        $this->assertExitCode(ReturnCodes::SUCCESS, $exitCode);
    }

    public function testGivenValidCiYamlAndBuildFails_WhenExecuted_ThenDisplaysConfirmationMessage()
    {
        $output = new BufferedOutput();
        $rumiFile = 'tests/Commands/Fixtures/rumiConfigBuildFails.yml';

        $exitCode = $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );

        $bufferedOutput = $output->fetch();

        $this->assertContains('Stage failed', $bufferedOutput);
        $this->assertContains('Stage: "Stage one"', $bufferedOutput);
        $this->assertContains('unknownCommand', $bufferedOutput);
        $this->assertExitCode(ReturnCodes::FAILED, $exitCode);
    }

    public function testGivenValidCiYamlAndBuildTimeOuts_WhenExecuted_ThenDisplaysTimeoutMessage()
    {
        $output = new BufferedOutput();
        $rumiFile = 'tests/Commands/Fixtures/rumiConfigBuildFails.yml';

        $exitCode = $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );

        $bufferedOutput = $output->fetch();

        $this->assertContains('Stage failed', $bufferedOutput);
        $this->assertContains('Stage: "Stage one"', $bufferedOutput);
        $this->assertContains('Process timed out after 1s', $bufferedOutput);
        $this->assertExitCode(ReturnCodes::FAILED, $exitCode);
    }

    public function testGivenJobsAreSuccessful_WhenRunIsStarted_ThenEventsAreTriggeredWithProperStatuses()
    {
        $listener = $this->prophesize(Listener::class);
        $listener->listenToAll(Argument::type(RunStartedEvent::class))->shouldBeCalled();
        $listener->listenToAll(Argument::type(RunFinishedEvent::class))->shouldBeCalled()->will(function ($args) {
            $this->assertEquals(RunFinishedEvent::STATUS_SUCCESS, $args[0]->getStatus());
        });

        $this->app->getEventDispatcher()->addListener(Events::RUN_STARTED, $listener);
        $this->app->getEventDispatcher()->addListener(Events::RUN_FINISHED, $listener);

        $output = new BufferedOutput();
        $rumiFile = 'tests/Commands/Fixtures/rumiConfigBuildIsOkay.yml';

        $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );
    }

    public function testGivenJobFails_WhenRunIsStarted_ThenEventsAreTriggeredWithProperStatuses()
    {
        $listener = $this->prophesize(Listener::class);
        $listener->listenToAll(Argument::type(RunStartedEvent::class))->shouldBeCalled();
        $listener->listenToAll(Argument::type(RunFinishedEvent::class))->shouldBeCalled()->will(function ($args) {
            $this->assertEquals(RunFinishedEvent::STATUS_FAILED, $args[0]->getStatus());
        });

        $this->app->getEventDispatcher()->addListener(Events::RUN_STARTED, $listener);
        $this->app->getEventDispatcher()->addListener(Events::RUN_FINISHED, $listener);

        $output = new BufferedOutput();
        $rumiFile = 'tests/Commands/Fixtures/rumiConfigBuildIsOkay.yml';

        $this->app->run(
            new StringInput(sprintf('run --%s=%s', RunCommand::CONFIG, $rumiFile)),
            $output
        );
    }

    /**
     * @param $expectedExitCode
     * @param $exitCode
     */
    private function assertExitCode($expectedExitCode, $exitCode)
    {
        $this->assertEquals(
            $expectedExitCode,
            $exitCode,
            sprintf('Expected exit code %s do not match. Got %s.', $expectedExitCode, $exitCode)
        );
    }
}

interface Listener
{
    public function listenToAll();
}
