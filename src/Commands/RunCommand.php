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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trivago\Rumi\Builders\JobConfigBuilder;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\RunFinishedEvent;
use Trivago\Rumi\Events\RunStartedEvent;
use Trivago\Rumi\Events\StageFinishedEvent;
use Trivago\Rumi\Events\StageStartedEvent;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Timer;

class RunCommand extends Command
{
    const CONFIG = 'config';
    const CONFIG_SHORT = 'c';
    const GIT_COMMIT = 'git_commit';
    const VOLUME = 'volume';

    /**
     * @var string|null
     */
    private $volume;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @param ContainerInterface       $container
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->configReader = $container->get('trivago.rumi.services.config_reader');
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run tests')
            ->addOption(
                self::CONFIG,
                self::CONFIG_SHORT,
                InputOption::VALUE_REQUIRED,
                'Configuration file to read',
                ConfigReader::CONFIG_FILE)
            ->addArgument(self::VOLUME, InputArgument::OPTIONAL, 'Docker volume containing data')
            ->addArgument(self::GIT_COMMIT, InputArgument::OPTIONAL, 'Commit id');
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
            if (trim($input->getArgument('volume')) != '') {
                $this->volume = $input->getArgument(self::VOLUME);
            } else {
                $this->volume = $this->getWorkingDir();
            }
            $timeTaken = Timer::execute(function () use ($input, $output) {
                $runConfig = $this->configReader->getConfig($this->getWorkingDir(), $input->getOption(self::CONFIG));

                /** @var JobConfigBuilder $jobConfigBuilder */
                $jobConfigBuilder = $this->container->get('trivago.rumi.job_config_builder');

                $this->eventDispatcher->dispatch(
                    Events::RUN_STARTED, new RunStartedEvent($runConfig)
                );

                foreach ($runConfig->getStages() as $stageName => $stageConfig) {
                    try {
                        $jobs = $jobConfigBuilder->build($stageConfig);

                        $this->eventDispatcher->dispatch(
                            Events::STAGE_STARTED,
                            new StageStartedEvent($stageName, $jobs)
                        );

                        $output->writeln(sprintf('<info>Stage: "%s"</info>', $stageName));

                        $time = Timer::execute(
                            function () use ($jobs, $output) {
                                $this
                                    ->container
                                    ->get('trivago.rumi.commands.run.stage_executor')
                                    ->executeStage($jobs, $this->volume, $output);
                            }
                        );

                        $output->writeln('<info>Stage completed: ' . $time . '</info>' . PHP_EOL);

                        $this->eventDispatcher->dispatch(
                            Events::STAGE_FINISHED,
                            new StageFinishedEvent(StageFinishedEvent::STATUS_SUCCESS, $stageName)
                        );
                    } catch (\Exception $e) {
                        $this->eventDispatcher->dispatch(
                            Events::STAGE_FINISHED,
                            new StageFinishedEvent(StageFinishedEvent::STATUS_FAILED, $stageName)
                        );

                        throw $e;
                    }
                }

                $this->eventDispatcher->dispatch(Events::RUN_FINISHED, new RunFinishedEvent(RunFinishedEvent::STATUS_SUCCESS));
            });

            $output->writeln('<info>Build successful: ' . $timeTaken . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            $this->eventDispatcher->dispatch(Events::RUN_FINISHED, new RunFinishedEvent(RunFinishedEvent::STATUS_FAILED));

            return $e->getCode() > 0 ? $e->getCode() : ReturnCodes::FAILED;
        }

        return 0;
    }
}
