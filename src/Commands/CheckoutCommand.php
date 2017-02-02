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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\GitProcessExecutor\GitCheckoutCommit;
use Trivago\Rumi\GitProcessExecutor\GitClone;
use Trivago\Rumi\GitProcessExecutor\GitMerge;

class CheckoutCommand extends CommandAbstract
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
     * @param GitClone $gitCloneProcess
     * @param GitMerge $gitMergeProcess
     * @param GitCheckoutCommit $gitCheckoutCommit
     */
    public function __construct(
        GitClone $gitCloneProcess,
        GitMerge $gitMergeProcess,
        GitCheckoutCommit $gitCheckoutCommit)
    {
        parent::__construct();
        $this->gitCloneProcess = $gitCloneProcess;
        $this->gitMergeProcess = $gitMergeProcess;
        $this->gitCheckoutCommitProcess = $gitCheckoutCommit;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('checkout')
            ->setDescription('Checkout code')
            ->addArgument('repository', InputArgument::REQUIRED, 'Repository url')
            ->addArgument('commit', InputArgument::REQUIRED, 'Commit id/branch name to checkout');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->gitCloneProcess->executeGitCloneBranch($input->getArgument('repository'), $output);

            $this->gitCheckoutCommitProcess->executeGitCheckoutCommitProcess($input->getArgument('commit'), $output);

            $this->gitMergeProcess->executeGitMergeBranchProcess($input->getOption(self::CONFIG), $output);

            $output->writeln('<info>Checkout done</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return $e->getCode();
        }

        return ReturnCodes::SUCCESS;
    }
}
