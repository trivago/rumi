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


use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\GitCheckoutCommitProcess;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitCheckoutCommitProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GitCheckoutCommitProcess
     */
    private $gitCheckoutCommitProcess;

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


    public function setUp() {
        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $this->gitCheckoutCommitProcess = new GitCheckoutCommitProcess (
            $this->gitCheckoutValidator->reveal(),
            $this->processFactory->reveal()
        );
    }

    public function testGivenCommitSha_WhenCommandExecuted_ThenReturnedOutputContainsGivenCommitSha()
    {
        $commitProcess = $this->prophesize(Process::class);
        $this->processFactory->getCheckoutCommitProcess('sha123')->willReturn($commitProcess->reveal());

        $this->gitCheckoutCommitProcess->executeGitCheckoutCommitProcess('sha123', $this->output);

        $this->assertContains('Checking out sha123', $this->output->fetch());
    }

}