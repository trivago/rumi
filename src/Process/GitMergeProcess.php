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

class GitMergeProcess
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;


    /**
     * GitMergeProcess constructor.
     * @param ConfigReader $configReader
     * @param GitCheckoutProcessFactory $gitCheckoutProcessFactory
     * @param GitCheckoutValidator $gitCheckoutValidator
     */
    public function __construct(
        ConfigReader $configReader,
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        GitCheckoutValidator $gitCheckoutValidator
        )
    {
        $this->configReader = $configReader;
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }


    /**
     * @param $workingDir
     * @param $configFile
     * @return null|string|void
     */
    public function getMergeBranch($workingDir, $configFile)
    {
        try {
            $configReader = $this->configReader;

            $config = $configReader->getRunConfig($workingDir, $configFile);

            if (!empty($config->getMergeBranch())) {
                return $config->getMergeBranch();
            }
        } catch (\Exception $e) {
        }

        return;
    }

    /**
     * @param $configFile
     * @param OutputInterface $output
     *
     * @param null $workingDir
     * @throws \Exception
     */
    public function executeGitMergeBranchProcess($configFile, OutputInterface $output, $workingDir = null)
    {
        $mergeBranch = $this->getMergeBranch($workingDir, $configFile);

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
}