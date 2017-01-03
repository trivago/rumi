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
use Trivago\Rumi\Process\GitCloneProcess;
use Trivago\Rumi\Process\GitProcessesExecution;

class CheckoutCommand extends CommandAbstract
{
    /**
     * @var GitProcessesExecution
     */
    private $gitProcessesExecution;

    /**
     * @var GitCloneProcess
     */
    private $gitCloneProcess;

    /**
     * @param GitProcessesExecution $gitProcessesExecution
     * @param GitCloneProcess $gitCloneProcess
     */
    public function __construct(GitProcessesExecution $gitProcessesExecution, GitCloneProcess $gitCloneProcess)
    {
        parent::__construct();
        $this->gitProcessesExecution = $gitProcessesExecution;
        $this->gitCloneProcess = $gitCloneProcess;
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->gitCloneProcess->executeGitCloneBranch($input->getArgument('repository'), $output);

            $this->gitProcessesExecution->executeGitCheckoutCommitProcess($input->getArgument('commit'), $output);

            $this->gitProcessesExecution->executeGitMergeBranchProcess($input->getOption(self::CONFIG), $output);

            $output->writeln('<info>Checkout done</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return $e->getCode();
        }

        return ReturnCodes::SUCCESS;
    }
}
