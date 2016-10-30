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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\ExecuteProcessEvent;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Process\GitCloneProcessFactory;
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
     * @var ExecuteProcessEvent
     */
    private $executeProcessEvent;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * RunCommand constructor.
     *
     * @param ContainerInterface $container
     * @param GitCloneProcessFactory $gitCloneProcessFactory
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ContainerInterface $container, GitCloneProcessFactory $gitCloneProcessFactory, EventDispatcherInterface $dispatcher)
    {
        parent::__construct();
        $this->container = $container;
        $this->gitCloneProcessFactory = $gitCloneProcessFactory;
        $this->dispatcher = $dispatcher;
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
//            /** @var GitCheckoutProcessFactory $processFactory */
            $processFactory = $this
                ->container
                ->get('trivago.rumi.process.git_checkout_process_factory');
//            $process = new GitCloneProcessFactory($processFactory);


            $output->writeln(
                $this->dispatcher->dispatch(
                    Events::GIT_CLONE_STARTED,
                    new ExecuteProcessEvent(
                        $this->gitCloneProcessFactory->GitCloneProcess(
                        $input, $output)
                    ))
//                $this->executeProcessEvent->executeProcess(
//                    $this->gitCloneProcessFactory->GitCloneProcess($input, $output, $processFactory))
            );
            $this->executeProcessEvent->validateGitProcess($this->gitCloneProcessFactory->GitCloneProcess($input, $output));

            $output->writeln('Checking out '.$input->getArgument('commit').' ');
            $process = $processFactory->getCheckoutCommitProcess($input->getArgument('commit'));

            $output->writeln($this->executeProcess($process));

            $mergeBranch = $this->getMergeBranch($input->getOption(self::CONFIG));
            if (!empty($mergeBranch)) {
                $output->writeln('Merging with '.$mergeBranch);
                try {
                    $process = $processFactory->getMergeProcess($mergeBranch);
                    $this->executeProcess($process);
                    $this->gitCheckoutValidator->checkStatus($process);
                } catch (\Exception $e) {
                    throw new \Exception('Can not clearly merge with '.$mergeBranch);
                }
            }

            $output->writeln('<info>Checkout done</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return $e->getCode();
        }

        return ReturnCodes::SUCCESS;
    }

//    /**
//     * @param $process
//     *
//     * @return string
//     */
//    protected function executeProcess(Process $process)
//    {
//        $time = Timer::execute(
//            function () use ($process) {
//                $process->run();
//            }
//        );
//
//        return $time;
//    }

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
