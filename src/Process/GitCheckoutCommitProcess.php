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

namespace Trivago\Rumi\Process;


use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitCheckoutCommitProcess
{
    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;


    public function __construct(
        GitCheckoutValidator $gitCheckoutValidator,
        GitCheckoutProcessFactory $gitCheckoutProcessFactory
    )
    {
        $this->gitCheckoutValidator = $gitCheckoutValidator;
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
    }

    /**
     * @param $commitSha
     * @param OutputInterface $output
     */
    public function executeGitCheckoutCommitProcess($commitSha, OutputInterface $output)
    {
        $output->writeln('Checking out '.$commitSha.' ');
        $process = $this->gitCheckoutProcessFactory->getCheckoutCommitProcess($commitSha);

        $process->run();
        $this->gitCheckoutValidator->checkStatus($process);
    }
}