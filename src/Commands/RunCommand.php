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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trivago\Rumi\Builders\JobConfigBuilder;
use Trivago\Rumi\Commands\Run\StageExecutor;
use Trivago\Rumi\Events;
use Trivago\Rumi\Events\RunFinishedEvent;
use Trivago\Rumi\Events\RunStartedEvent;
use Trivago\Rumi\Events\StageFinishedEvent;
use Trivago\Rumi\Events\StageStartedEvent;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\VCSInfo\GitInfo;
use Trivago\Rumi\Models\VCSInfo\VCSInfoInterface;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Timer;

class RunCommand extends CommandAbstract
{
    const GIT_COMMIT = 'git_commit';
    const GIT_URL = 'git_url';
    const GIT_BRANCH = 'git_branch';

    const VOLUME = 'volume';

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var StageExecutor
     */
    private $stageExecutor;

    /**
     * @var JobConfigBuilder
     */
    private $jobConfigBuilder;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param ConfigReader             $configReader
     * @param StageExecutor            $stageExecutor
     * @param JobConfigBuilder         $jobConfigBuilder
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigReader $configReader,
        StageExecutor $stageExecutor,
        JobConfigBuilder $jobConfigBuilder
) {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
        $this->configReader = $configReader;
        $this->stageExecutor = $stageExecutor;
        $this->jobConfigBuilder = $jobConfigBuilder;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('run')
            ->setDescription('Run tests')
            ->addArgument(self::VOLUME, InputArgument::OPTIONAL, 'Docker volume containing data')
            ->addArgument(self::GIT_COMMIT, InputArgument::OPTIONAL, 'Commit ID')
            ->addArgument(self::GIT_URL, InputArgument::OPTIONAL, 'Git checkout url')
            ->addArgument(self::GIT_BRANCH, InputArgument::OPTIONAL, 'Git checkout branch');
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

        return $this->workingDir.'/';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (trim($input->getArgument('volume')) != '') {
                $volume = $input->getArgument(self::VOLUME);
            } else {
                $volume = $this->getWorkingDir();
            }

            $VCSInfo = new GitInfo(
                $input->getArgument(self::GIT_URL),
                $input->getArgument(self::GIT_COMMIT),
                $input->getArgument(self::GIT_BRANCH)
            );

            $configFilePath = $input->getOption(self::CONFIG);

            $runConfig = $this->configReader->getRunConfig($this->getWorkingDir(), $configFilePath);

            $this->eventDispatcher->dispatch(Events::RUN_STARTED, new RunStartedEvent($runConfig));

            $timeTaken = Timer::execute(function () use ($runConfig, $output, $VCSInfo, $volume) {
                $this->startRun($runConfig, $output, $VCSInfo, $volume);
            });

            $this->eventDispatcher->dispatch(Events::RUN_FINISHED, new RunFinishedEvent(RunFinishedEvent::STATUS_SUCCESS));

            $output->writeln('<info>Build successful: '.$timeTaken.'</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            $this->eventDispatcher->dispatch(Events::RUN_FINISHED, new RunFinishedEvent(RunFinishedEvent::STATUS_FAILED));

            return $e->getCode() > 0 ? $e->getCode() : ReturnCodes::FAILED;
        }

        return 0;
    }

    /**
     * @param RunConfig $runConfig
     * @param OutputInterface $output
     * @param VCSInfoInterface $VCSInfo
     * @param string $volume
     * @throws \Exception
     *
     * My intention is to move it to RunExecutor class
     */
    private function startRun(RunConfig $runConfig, OutputInterface $output, VCSInfoInterface $VCSInfo, string $volume){
        foreach ($runConfig->getStagesCollection() as $stageName => $stageConfig) {
            try {
                $jobs = $this->jobConfigBuilder->build($stageConfig);

                $this->eventDispatcher->dispatch(
                    Events::STAGE_STARTED,
                    new StageStartedEvent($stageName, $jobs)
                );

                $output->writeln(sprintf('<info>Stage: "%s"</info>', $stageName));

                $time = Timer::execute(
                    function () use ($jobs, $output, $VCSInfo, $volume) {
                        $this->stageExecutor->executeStage($jobs, $volume, $output, $VCSInfo);
                    }
                );

                $output->writeln('<info>Stage completed: '.$time.'</info>'.PHP_EOL);

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


    }
}
