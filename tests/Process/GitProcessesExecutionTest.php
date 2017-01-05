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

use Error;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Process\GitProcessesExecution;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

/**
 * @covers \Trivago\Rumi\Process\GitProcessesExecution
 */
class GitProcessesExecutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $processFactory;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var GitProcessesExecution
     */
    private $gitProcessesExecution;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var BufferedOutput
     */
    private $output;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        $this->configReader = $this->prophesize(ConfigReader::class);
        $this->input = $this->prophesize(InputInterface::class)->reveal();

        $this->gitProcessesExecution = new GitProcessesExecution(
            $this->gitCheckoutValidator->reveal(),
            $this->processFactory->reveal(),
            $this->configReader->reveal()
        );

        $this->gitProcessesExecution->setWorkingDir(vfsStream::url('directory'));
    }
//
//    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt()
//    {
//        $runConfig = $this->prophesize(RunConfig::class);
//        $runConfig->getMergeBranch()->willReturn('origin/master');
//
//        $this->configReader->getRunConfig(vfsStream::url('directory').'/', 'config_file')->willReturn(
//            $runConfig->reveal()
//        );
//
//        $mergeProcess = $this->prophesize(Process::class);
//        $mergeProcess->run();
//
//        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal());
//
//        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());
//        $this->gitProcessesExecution->executeGitMergeBranchProcess('config_file', $this->output);
//
//        $this->assertContains('Merging with origin/master', $this->output->fetch());
//    }
//
//    /**
//     * @expectedException \Exception
//     * @expectedExceptionMessage Can not clearly merge with origin/master
//     */
//    public function testGivenMergeFails_WhenCommandExecuted_ThenItReturnsValidOutput()
//    {
//        $runConfig = $this->prophesize(RunConfig::class);
//        $runConfig->getMergeBranch()->willReturn('origin/master');
//
//        $this->configReader->getRunConfig(vfsStream::url('directory').'/', 'config_file')->willReturn(
//            $runConfig->reveal()
//        );
//
//        $mergeProcess = $this->prophesize(Process::class);
//        $mergeProcess->run()->shouldBeCalled();
//
//        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal())->willThrow(new \Exception('Error'));
//        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());
//
//        $this->gitProcessesExecution->executeGitMergeBranchProcess('config_file', $this->output);
//    }

    public function testGivenCommitSha_WhenCommandExecuted_ThenReturnedOutputContainsGivenCommitSha()
    {
        $commitProcess = $this->prophesize(Process::class);
        $this->processFactory->getCheckoutCommitProcess('sha123')->willReturn($commitProcess->reveal());

        $this->gitProcessesExecution->executeGitCheckoutCommitProcess('sha123', $this->output);

        $this->assertContains('Checking out sha123', $this->output->fetch());
    }
}
