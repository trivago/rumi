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

namespace Trivago\Rumi\GitProcessExecutor;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitMergeProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $processFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var GitMerge
     */
    private $gitMerge;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        $this->configReader = $this->prophesize(ConfigReader::class);

        $this->gitMerge = new GitMerge(
            $this->configReader->reveal(),
            $this->processFactory->reveal(),
            $this->gitCheckoutValidator->reveal()
        );
    }

    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt()
    {
        touch(vfsStream::url('directory'));

        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn('origin/master');

        $this->configReader->getRunConfig(vfsStream::url('directory'), 'config_file')->willReturn(
            $runConfig->reveal()
        );

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();

        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal());

        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());
        $this->gitMerge->executeGitMergeBranchProcess('config_file', $this->output, vfsStream::url('directory'));

        $this->assertContains('Merging with origin/master', $this->output->fetch());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Can not clearly merge with origin/master
     */
    public function testGivenMergeFails_WhenCommandExecuted_ThenItReturnsValidOutput()
    {
        touch(vfsStream::url('directory').'/git');

        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn('origin/master');

        $this->configReader->getRunConfig(vfsStream::url('directory'), 'config_file')->willReturn(
            $runConfig->reveal()
        );

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();

        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal())->willThrow(new \Exception('Error'));
        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $this->gitMerge->executeGitMergeBranchProcess('config_file', $this->output, vfsStream::url('directory'));
    }

    public function testGivenConfigReaderThrowsException_whenIExecuteGitMergeBranchProcess_thenNothingIsReturned ()
    {
        touch(vfsStream::url('directory').'/git');

        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn(null);

        $this->configReader->getRunConfig(vfsStream::url('directory'), 'config_file')->willThrow(new \Exception('Error'));

        $mergeProcess = $this->prophesize(Process::class);
        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $this->gitMerge->executeGitMergeBranchProcess('config_file', $this->output, vfsStream::url('directory'));

        $this->assertEquals('', $this->output->fetch());
    }
}
