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
use Trivago\Rumi\Process\GitCheckoutCommitProcess;
use Trivago\Rumi\Process\GitCloneProcess;
use Trivago\Rumi\Process\GitMergeProcess;

class CheckoutCommand extends CommandAbstract
{
    /**
     * @var GitCloneProcess
     */
    private $gitCloneProcess;

    /**
     * @var GitMergeProcess
     */
    private $gitMergeProcess;

    /**
     * @var GitCheckoutCommitProcess
     */
    private $gitCheckoutCommitProcess;

    /**
     * @param GitCloneProcess $gitCloneProcess
     * @param GitMergeProcess $gitMergeProcess
     * @param GitCheckoutCommitProcess $gitCheckoutCommitProcess
     */
    public function __construct(
        GitCloneProcess $gitCloneProcess,
        GitMergeProcess $gitMergeProcess,
        GitCheckoutCommitProcess $gitCheckoutCommitProcess)
    {
        parent::__construct();
        $this->gitCloneProcess = $gitCloneProcess;
        $this->gitMergeProcess = $gitMergeProcess;
        $this->gitCheckoutCommitProcess = $gitCheckoutCommitProcess;
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
