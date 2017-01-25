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

use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitClone
{
    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    public function __construct(
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        GitCheckoutValidator $gitCheckoutValidator
    ) {
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }

    /**
     * @param $workingDir
     * @param $repositoryUrl
     * @param OutputInterface $output
     */
    public function executeGitCloneBranch($repositoryUrl, OutputInterface $output, $workingDir = null)
    {
        if (!file_exists($workingDir.'/.git')) {
            $output->writeln('Cloning...');
            $process =
                $this->gitCheckoutProcessFactory->getFullCloneProcess($repositoryUrl);
        } else {
            $output->writeln('Fetching changes...');
            $process =
                $this->gitCheckoutProcessFactory->getFetchProcess();
        }

        $process->run();
        $this->gitCheckoutValidator->checkStatus($process);
    }
}