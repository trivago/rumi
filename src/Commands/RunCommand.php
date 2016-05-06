<?php

namespace jakubsacha\Rumi\Commands;

use jakubsacha\Rumi\Builders\JobConfigBuilder;
use jakubsacha\Rumi\Events\JobFinishedEvent;
use jakubsacha\Rumi\Events\JobStartedEvent;
use jakubsacha\Rumi\Events\RunFinishedEvent;
use jakubsacha\Rumi\Events\RunStartedEvent;
use jakubsacha\Rumi\Events\StageFinishedEvent;
use jakubsacha\Rumi\Events\StageStartedEvent;
use jakubsacha\Rumi\Builders\DockerComposeYamlBuilder;
use jakubsacha\Rumi\Events;
use jakubsacha\Rumi\Exceptions\CommandFailedException;
use jakubsacha\Rumi\Models\RunConfig;
use jakubsacha\Rumi\Models\RunningCommand;
use jakubsacha\Rumi\Models\JobConfig;
use jakubsacha\Rumi\Process\RunningProcessesFactory;
use jakubsacha\Rumi\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Parser;

class RunCommand extends Command
{
    const CONFIG_FILE = '.rumi.yml';
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
     * @param ContainerInterface       $container
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run tests')
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

        return $this->workingDir.'/';
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
                $runConfig = $this->readCiConfigFile();

                /** @var JobConfigBuilder $jobConfigBuilder */
                $jobConfigBuilder = $this->container->get('jakubsacha.rumi.job_config_builder');

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
                                $this->executeStage($jobs, $output);
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

                $this->eventDispatcher->dispatch(Events::RUN_FINISHED, new RunFinishedEvent(RunFinishedEvent::STATUS_SUCCESS));
            });

            $output->writeln('<info>Build successful: '.$timeTaken.'</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            $this->eventDispatcher->dispatch(Events::RUN_FINISHED, new RunFinishedEvent(RunFinishedEvent::STATUS_FAILED));

            return $e->getCode() > 0 ? $e->getCode() : ReturnCodes::FAILED;
        }

        return 0;
    }

    /**
     * @return RunConfig
     * 
     * @throws \Exception
     */
    private function readCiConfigFile()
    {
        if (!file_exists($this->getWorkingDir().self::CONFIG_FILE)) {
            throw new \Exception('Required file \''.self::CONFIG_FILE.'\' does not exist', ReturnCodes::RUMI_YML_DOES_NOT_EXIST);
        }
        $parser = new Parser();

        $ciConfig = $parser->parse(file_get_contents($this->getWorkingDir().self::CONFIG_FILE));

        return new RunConfig($ciConfig['stages']);
    }

    private function executeStage($jobs, OutputInterface $output)
    {
        $this->handleProcesses($output, $this->startStageProcesses($jobs));
    }

    /**
     * @param JobConfig[] $jobs
     *
     * @return array
     */
    private function startStageProcesses($jobs)
    {
        $processes = [];

        /** @var DockerComposeYamlBuilder $dockerComposeYamlBuilder */
        $dockerComposeYamlBuilder = $this->container->get('jakubsacha.rumi.docker_compose_yaml_builder');

        /** @var RunningProcessesFactory $runningProcessFactory */
        $runningProcessFactory = $this->container->get('jakubsacha.rumi.process.running_processes_factory');

        foreach ($jobs as $jobConfig) {
            $runningCommand = new RunningCommand(
                $jobConfig,
                $dockerComposeYamlBuilder->build($jobConfig, $this->volume),
                $runningProcessFactory
            );

            $this->eventDispatcher->dispatch(Events::JOB_STARTED, new JobStartedEvent($jobConfig->getName()));

            $runningCommand->start();

            $processes[] = $runningCommand;
        }

        return $processes;
    }

    /**
     * @param OutputInterface  $output
     * @param RunningCommand[] $processes
     *
     * @throws \Exception
     */
    private function handleProcesses(OutputInterface $output, $processes)
    {
        try {
            while (count($processes)) {
                foreach ($processes as $id => $runningCommand) {
                    if ($runningCommand->isRunning()) {
                        continue;
                    }
                    unset($processes[$id]);

                    $output->writeln(sprintf('<info>Executing job: %s</info>', $runningCommand->getJobName()));
                    $output->write($runningCommand->getOutput());

                    $isSuccessful = $runningCommand->getProcess()->isSuccessful();

                    $this->eventDispatcher->dispatch(
                        Events::JOB_FINISHED, new JobFinishedEvent(
                            $isSuccessful ? JobFinishedEvent::STATUS_SUCCESS : JobFinishedEvent::STATUS_FAILED,
                            $runningCommand->getJobName(),
                            $runningCommand->getOutput()
                        )
                    );

                    $runningCommand->tearDown();

                    if (!$isSuccessful) {
                        $output->write($runningCommand->getProcess()->getErrorOutput());
                        throw new CommandFailedException($runningCommand->getCommand());
                    }
                }
                usleep(500000);
            }
        } catch (CommandFailedException $e) {
            $output->writeln("<error>Command '".$e->getMessage()."' failed</error>");
            if (!empty($processes)) {
                $output->writeln('Shutting down jobs in background...', OutputInterface::VERBOSITY_VERBOSE);
                foreach ($processes as $runningCommand) {
                    $output->writeln('- '.$runningCommand->getCommand(), OutputInterface::VERBOSITY_VERBOSE);

                    $this->eventDispatcher->dispatch(
                        Events::JOB_FINISHED,
                        new JobFinishedEvent(
                            JobFinishedEvent::STATUS_ABORTED,
                            $runningCommand->getJobName(),
                            $runningCommand->getOutput()
                        )
                    );
                    $runningCommand->tearDown();
                }
            }

            throw new \Exception('Stage failed', ReturnCodes::FAILED);
        }
    }
}
