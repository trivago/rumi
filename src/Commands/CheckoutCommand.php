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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Timer;

class CheckoutCommand extends Command
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
            } else {
                $output->writeln('Fetching changes...');
                $process =
                    $processFactory->getFetchProcess();
            }

            $output->writeln($this->executeProcess($process));

            $output->writeln('Checking out ' . $input->getArgument('commit') . ' ');
            $process = $processFactory->getCheckoutCommitProcess($input->getArgument('commit'));

            $output->writeln($this->executeProcess($process));

            $mergeBranch = $this->getMergeBranch();
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
    protected function executeProcess(Process $process)
    {
        $time = Timer::execute(
            function () use ($process) {
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new \Exception($process->getErrorOutput());
                }
            }
        );

        return $time;
    }

    private function getMergeBranch()
    {
        try {
            $config = $this->readCiConfigFile();

            if (isset($config['merge_branch'])) {
                return $config['merge_branch'];
            }
        } catch (\Exception $e) {
        }

        return;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    private function readCiConfigFile()
    {
        if (!file_exists($this->getWorkingDir() . RunCommand::CONFIG_FILE)) {
            throw new \Exception('Required file \'' . RunCommand::CONFIG_FILE . '\' does not exist');
        }
        $parser = new Parser();

        return $parser->parse(file_get_contents($this->getWorkingDir() . RunCommand::CONFIG_FILE));
    }
}
