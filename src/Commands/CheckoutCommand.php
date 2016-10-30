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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\GitCloneProcessFactory;
use Trivago\Rumi\Process\GitMergeProcessFactory;
use Trivago\Rumi\Timer;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class CheckoutCommand extends CommandAbstract
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @var GitCloneProcessFactory
     */
    private $gitCloneProcessFactory;

    /**
     * @var GitMergeProcessFactory
     */
    private $gitMergeProcessFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * RunCommand constructor.
     *
     * @param ContainerInterface $container
     * @param GitCloneProcessFactory $gitCloneProcessFactory
     * @param GitMergeProcessFactory $gitMergeProcessFactory
     * @param GitCheckoutValidator $gitCheckoutValidator
     */
    public function __construct(
        ContainerInterface $container,
        GitCloneProcessFactory $gitCloneProcessFactory,
        GitMergeProcessFactory $gitMergeProcessFactory,
        GitCheckoutValidator $gitCheckoutValidator)
    {
        parent::__construct();
        $this->container = $container;
        $this->gitCloneProcessFactory = $gitCloneProcessFactory;
        $this->gitMergeProcessFactory = $gitMergeProcessFactory;
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('checkout')
            ->setDescription('Checkout code')
            ->addArgument('repository', InputArgument::REQUIRED, 'Repository url')
            ->addArgument('commit', InputArgument::REQUIRED, 'Commit id/branch name to checkout');

        $this->workingDir = getcwd();
    }

    /**
     * @param $dir
     */
    public function setWorkingDir($dir)
    {
        $this->workingDir = $dir;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $processFactory = $this
                ->container
                ->get('trivago.rumi.process.git_checkout_process_factory');

            $output->writeln(
                $this->executeProcess(
                $this->gitCloneProcessFactory->GitCloneProcess($input, $output))
            );

            $process = $processFactory->getCheckoutCommitProcess($input, $output, $input->getArgument('commit'));
            $output->writeln($this->executeProcess($process));

            $process = $this->gitMergeProcessFactory->mergeBranchProcess($input, $output);
            $this->executeProcess($process);

            $output->writeln('<info>Checkout done</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return $e->getCode();
        }

        return ReturnCodes::SUCCESS;
    }

    /**
     * @param $process
     *
     * @return string
     */
    protected function executeProcess(Process $process)
    {
        $time = Timer::execute(
            function () use ($process) {
                $process->run();
                $this->gitCheckoutValidator->checkStatus($process);
            }
        );

        return $time;
    }
}
