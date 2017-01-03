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
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitProcessesExecution
{
    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var ConfigReader
     */
    private $configReader;

    //TODO to remove
    /**
     * @var string
     */
    private $workingDir;

    public function __construct(
        GitCheckoutValidator $gitCheckoutValidator,
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        ConfigReader $configReader)
    {
        $this->gitCheckoutValidator = $gitCheckoutValidator;
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->configReader = $configReader;
    }

    //@TODO to remove
    /**
     * @param $dir
     */
    public function setWorkingDir($dir)
    {
        $this->workingDir = $dir;
    }

    //@TODO to remove
    /**
     * @codeCoverageIgnore
     */
    private function getWorkingDir()
    {
        if (empty($this->workingDir)) {
            return;
        }

        return $this->workingDir.'/';
    }

    /**
     * @param $configFile
     *
     * @return null|string|void
     */
    public function getMergeBranch($configFile)
    {
        try {
            $configReader = $this->configReader;

            $config = $configReader->getRunConfig($this->getWorkingDir(), $configFile);

            if (!empty($config->getMergeBranch())) {
                return $config->getMergeBranch();
            }
        } catch (\Exception $e) {
        }

        return;
    }

//    /**
//     * @param $repositoryUrl
//     * @param OutputInterface $output
//     */
//    public function executeGitCloneBranch($repositoryUrl, OutputInterface $output)
//    {
//        if (!file_exists($this->getWorkingDir().'.git')) {
//            $output->writeln('Cloning...');
//            $process =
//                $this->gitCheckoutProcessFactory->getFullCloneProcess($repositoryUrl);
//        } else {
//            $output->writeln('Fetching changes...');
//            $process =
//                $this->gitCheckoutProcessFactory->getFetchProcess();
//        }
//
//        $process->run();
//        $this->gitCheckoutValidator->checkStatus($process);
//    }

    /**
     * @param $configFile
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    public function executeGitMergeBranchProcess($configFile, OutputInterface $output)
    {
        $mergeBranch = $this->getMergeBranch($configFile);

        if (!empty($mergeBranch)) {
            $output->writeln('Merging with '.$mergeBranch);
            try {
                $process = $this->gitCheckoutProcessFactory->getMergeProcess($mergeBranch);
                $process->run();
                $this->gitCheckoutValidator->checkStatus($process);
            } catch (\Exception $e) {
                throw new \Exception('Can not clearly merge with '.$mergeBranch);
            }
        }
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
