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
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Process\GitProcess;
use Trivago\Rumi\Timer;

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
     * RunCommand constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
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

    /**
     * @codeCoverageIgnore
     */
    private function getWorkingDir()
    {
        if (empty($this->workingDir)) {
            return;
        }

        return $this->workingDir . '/';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var GitCheckoutProcessFactory $processFactory */
            $processFactory = $this
                ->container
                ->get('trivago.rumi.process.git_checkout_process_factory');

            if (!file_exists($this->getWorkingDir() . '.git')) {
                $output->writeln('Cloning...');
                $process =
                    $processFactory->getFullCloneProcess($input->getArgument('repository'));
            }
            else {
                $output->writeln('Fetching changes...');
                $process =
                    $processFactory->getFetchProcess();
            }

            $output->writeln($this->executeProcess($process));

            $output->writeln('Checking out ' . $input->getArgument('commit') . ' ');
            $process = $processFactory->getCheckoutCommitProcess($input->getArgument('commit'));

            $output->writeln($this->executeProcess($process));

            $mergeBranch = $this->getMergeBranch($input->getOption(self::CONFIG));
            if (!empty($mergeBranch)) {
                $output->writeln('Merging with ' . $mergeBranch);
                try {
                    $this->executeProcess($processFactory->getMergeProcess($mergeBranch));
                } catch (\Exception $e) {
                    throw new \Exception('Can not clearly merge with ' . $mergeBranch);
                }
            }

            $output->writeln('<info>Checkout done</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return -1;
        }

        return 0;
    }

    /**
     * @param $process
     *
     * @return string
     */
    protected function executeProcess(GitProcess $process)
    {
        $time = Timer::execute(
            function () use ($process) {
                $process->run();
                $process->checkStatus();
            }
        );

        return $time;
    }

    private function getMergeBranch($configFile)
    {
        try {
            $configReader = $this->container->get('trivago.rumi.services.config_reader');

            $config = $configReader->getConfig($this->getWorkingDir(), $configFile);

            if (!empty($config->getMergeBranch())) {
                return $config->getMergeBranch();
            }
        } catch (\Exception $e) {
        }

        return;
    }
}
