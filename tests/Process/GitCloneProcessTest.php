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

namespace Trivago\Rumi\Services;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Process\GitCloneProcess;
use Trivago\Rumi\Resources\WorkingDirTrait;
use Trivago\Rumi\Validators\GitCheckoutValidator;

/**
 * @covers \Trivago\Rumi\Process\GitCloneProcess
 */
class GitCloneProcessTest extends \PHPUnit_Framework_TestCase
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
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var GitCloneProcess
     */
    private $gitCloneProcess;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $this->gitCloneProcess = new GitCloneProcess(
            $this->processFactory->reveal(),
            $this->gitCheckoutValidator->reveal()
        );
    }

    public function testGivenWorkingDirIsEmpty_WhenCommandExecuted_ThenFullCheckoutIsDone()
    {
        touch(vfsStream::url('directory').'/.git');
        $cloneProcess = $this->prophesize(Process::class);

        $this->processFactory->getFullCloneProcess('repo_url')->willReturn($cloneProcess->reveal());
        $this->gitCloneProcess->executeGitCloneBranch('repo_url', $this->output, null);

        $this->assertContains('Cloning...', $this->output->fetch());
    }

    public function testGivenWorkingDirContainsDotGit_WhenCommandExecuted_ThenFetchIsDone()
    {
        touch(vfsStream::url('directory').'/.git');
        $fetchProcess = $this->prophesize(Process::class);

        $this->processFactory->getFetchProcess()->willReturn($fetchProcess->reveal());
        $this->gitCloneProcess->executeGitCloneBranch('repo_url', $this->output, vfsStream::url('directory'));

        $this->assertContains('Fetching changes...', $this->output->fetch());
    }
}