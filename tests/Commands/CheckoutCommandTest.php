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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Trivago\Rumi\GitProcessExecutor\GitCheckoutCommit;
use Trivago\Rumi\GitProcessExecutor\GitClone;
use Trivago\Rumi\GitProcessExecutor\GitMerge;


/**
 * @covers \Trivago\Rumi\Commands\CheckoutCommand
 */
class CheckoutCommandTest extends TestCase
{
    /**
     * @var GitClone
     */
    private $gitCloneProcess;

    /**
     * @var GitMerge
     */
    private $gitMergeProcess;

    /**
     * @var GitCheckoutCommit
     */
    private $gitCheckoutCommitProcess;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var CheckoutCommand
     */
    private $SUT;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var string
     */
    private $workingDir;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();
        $this->input = $this->prophesize(InputInterface::class);

        $this->gitCloneProcess = $this->prophesize(GitClone::class);
        $this->gitMergeProcess = $this->prophesize(GitMerge::class);
        $this->gitCheckoutCommitProcess = $this->prophesize(GitCheckoutCommit::class);

        $this->SUT = new CheckoutCommand(
            $this->gitCloneProcess->reveal(),
            $this->gitMergeProcess->reveal(),
            $this->gitCheckoutCommitProcess->reveal()
        );
    }

    public function testGivenGitCloneBranchIsExecuted_WhenProcessIsSuccessful_ThenFullCheckoutIsDone()
    {
        $this->gitCloneProcess->executeGitCloneBranch($this->input, $this->output);

        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        $this->assertContains('Checkout done', $this->output->fetch());
    }

    public function testGivenGitCloneBranchIsExecuted_WhenProcessFailed_ThenErrorIsDisplayed()
    {
        $this->gitCloneProcess->executeGitCloneBranch($this->input, $this->output)->willThrow(new \Exception('Error'));

        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        $this->assertContains('error', $this->output->fetch());
    }
}
