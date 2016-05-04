<?php

namespace Trivago\Rumi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Parser;
use Trivago\Rumi\Builders\DockerComposeYamlBuilder;
use Trivago\Rumi\Exceptions\CommandFailedException;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\RunningCommand;
use Trivago\Rumi\Timer;

class RunCommand extends Command
{
    const CONFIG_FILE = '.rumi.yml';

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
            ->setName('run')
            ->setDescription('Run tests')
            ->addArgument('volume', InputArgument::OPTIONAL, 'Docker volume containing data');
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
                $this->volume = $input->getArgument('volume');
            } else {
                $this->volume = $this->getWorkingDir();
            }

            $time = Timer::execute(function () use ($output) {
                $ciConfig = $this->readCiConfigFile();
                $jobConfigBuilder = $this->container->get('rumi.job_config_builder');

                foreach ($ciConfig['stages'] as $stageName => $stageConfig) {
                    $jobs = $jobConfigBuilder->build($stageConfig);

                    $output->writeln(sprintf('<info>Stage: "%s"</info>', $stageName));

                    $time = Timer::execute(
                        function () use ($jobs, $output) {
                            $this->executeStage($jobs, $output);
                        }
                    );

                    $output->writeln('<info>Stage completed: ' . $time . '</info>' . PHP_EOL);
                }
            });

            $output->writeln('<info>Build successful: ' . $time . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return $e->getCode() > 0 ? $e->getCode() : ReturnCodes::FAILED;
        }

        return 0;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function readCiConfigFile()
    {
        if (!file_exists($this->getWorkingDir() . self::CONFIG_FILE)) {
            throw new \Exception('Required file \'' . self::CONFIG_FILE . '\' does not exist', ReturnCodes::RUMI_YML_DOES_NOT_EXIST);
        }
        $parser = new Parser();

        return $parser->parse(file_get_contents($this->getWorkingDir() . self::CONFIG_FILE));
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
        $dockerComposeYamlBuilder = $this->container->get('rumi.docker_compose_yaml_builder');

        /** @var RunningProcessesFactory $runningProcessFactory */
        $runningProcessFactory = $this->container->get('rumi.process.running_processes_factory');

        foreach ($jobs as $jobConfig) {
            $runningCommand = new RunningCommand(
                $jobConfig,
                $dockerComposeYamlBuilder->build($jobConfig, $this->volume),
                $runningProcessFactory
            );

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
                    $output->writeln(sprintf('<info>Executing job: %s</info>', $runningCommand->getJobName()));
                    $output->write($runningCommand->getOutput());
                    if (!$runningCommand->getProcess()->isSuccessful()) {
                        $output->write($runningCommand->getProcess()->getErrorOutput());
                        throw new CommandFailedException($runningCommand->getCommand());
                    }

                    $runningCommand->tearDown();
                    unset($processes[$id]);
                }
                usleep(500000);
            }
        } catch (CommandFailedException $e) {
            $output->writeln("<error>Command '" . $e->getMessage() . "' failed</error>");
            if (!empty($processes)) {
                $output->writeln('Shutting down jobs in background...', OutputInterface::VERBOSITY_VERBOSE);
                foreach ($processes as $runningCommand) {
                    $output->writeln('- ' . $runningCommand->getCommand(), OutputInterface::VERBOSITY_VERBOSE);
                    $runningCommand->tearDown();
                }
            }

            throw new \Exception('Stage failed', ReturnCodes::FAILED);
        }
    }
}
