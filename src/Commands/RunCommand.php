<?php

namespace jakubsacha\Rumi\Commands;

use jakubsacha\Rumi\Builders\DockerComposeYamlBuilder;
use jakubsacha\Rumi\Exceptions\CommandFailedException;
use jakubsacha\Rumi\Models\RunningCommand;
use jakubsacha\Rumi\Models\JobConfig;
use jakubsacha\Rumi\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Parser;

class RunCommand extends Command
{
    const CONFIG_FILE = '.rumi.yml';

    private $volume;
    /**
     * @var DockerComposeYamlBuilder
     */
    private $oDockerComposeYmlBuilder;

    /**
     * @var string
     */
    private $sWorkingDir;
    /**
     * @var ContainerInterface
     */
    private $oContainer;

    /**
     * RunCommand constructor.
     * @param ContainerInterface $oContainer
     */
    public function __construct(ContainerInterface $oContainer)
    {
        parent::__construct();
        $this->oContainer = $oContainer;
    }


    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run tests')
            ->addArgument('volume', InputArgument::OPTIONAL, "Docker volume containing data");
        $this->sWorkingDir = getcwd();
    }

    /**
     * @param $dir
     */
    public function setWorkingDir($dir)
    {
        $this->sWorkingDir = $dir;
    }

    /**
     * @codeCoverageIgnore
     */
    private function getWorkingDir()
    {
        if (empty($this->sWorkingDir))
        {
            return null;
        }
        return $this->sWorkingDir . '/';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            if (trim($input->getArgument('volume')) != "" ){
                $this->volume = $input->getArgument('volume');
            }
            else
            {
                $this->volume = $this->getWorkingDir();
            }
            $iTime = Timer::execute(function() use ($output){
                $aCI = $this->readCiConfigFile();
                $oJobConfigBuilder = $this->oContainer->get('jakubsacha.rumi.job_config_builder');

                foreach ($aCI['stages'] as $sStageName => $aStageConfig)
                {
                    $aJobs = $oJobConfigBuilder->build($aStageConfig);

                    $output->writeln(sprintf('<info>Stage: "%s"</info>', $sStageName));

                    $iTime = Timer::execute(
                        function() use ($aJobs, $output) {
                            $this->executeStage($aJobs, $output);
                        }
                    );

                    $output->writeln("<info>Stage completed: ".$iTime."</info>" . PHP_EOL);
                }
            });

            $output->writeln("<info>Build successful: ".$iTime."</info>");
        } catch (\Exception $e)
        {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            return $e->getCode() > 0 ? $e->getCode(): ReturnCodes::FAILED;
        }
        return 0;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function readCiConfigFile()
    {
        if (!file_exists($this->getWorkingDir().self::CONFIG_FILE))
        {
            throw new \Exception('Required file \'' . RunCommand::CONFIG_FILE . '\' does not exist', ReturnCodes::RUMI_YML_DOES_NOT_EXIST);
        }
        $aParser = new Parser();

        return $aParser->parse(file_get_contents($this->getWorkingDir().self::CONFIG_FILE));
    }

    private function executeStage($aJobs, OutputInterface $output)
    {
        $this->handleProcesses($output, $this->startStageProcesses($aJobs));
    }

    /**
     * @param JobConfig[] $aJobs
     * @return array
     */
    private function startStageProcesses($aJobs)
    {
        $_oDockerComposeYamlBuilder = $this->oContainer->get('jakubsacha.rumi.docker_compose_yaml_builder');

        $this->oDockerComposeYmlBuilder = $this->oContainer->get('jakubsacha.rumi.docker_compose_yaml_builder');
        $aProcesses = [];
        foreach ($aJobs as $oJobConfig)
        {
            $oRunningCommand = new RunningCommand(
                $oJobConfig,
                $_oDockerComposeYamlBuilder->build($oJobConfig, $this->volume),
                $this->oContainer->get('jakubsacha.rumi.process.running_processes_factory')
            );

            $oRunningCommand->start();

            $aProcesses[] = $oRunningCommand;
        }

        return $aProcesses;
    }

    /**
     * @param OutputInterface $output
     * @param RunningCommand[] $aProcesses
     * @throws \Exception
     */
    private function handleProcesses(OutputInterface $output, $aProcesses)
    {
        try
        {
            while (count($aProcesses))
            {
                foreach ($aProcesses as $id => $oRunningCommand)
                {
                    if ($oRunningCommand->isRunning())
                    {
                        continue;
                    }
                    $output->writeln(sprintf('<info>Executing job: %s</info>', $oRunningCommand->getJobName()));
                    $output->write($oRunningCommand->getOutput());
                    if (!$oRunningCommand->getProcess()->isSuccessful())
                    {
                        $output->write($oRunningCommand->getProcess()->getErrorOutput());
                        throw new CommandFailedException($oRunningCommand->getCommand());
                    }

                    $oRunningCommand->tearDown();
                    unset($aProcesses[$id]);
                }
                usleep(500000);
            }
        }
        catch (CommandFailedException $e)
        {
            $output->writeln("<error>Command '".$e->getMessage()."' failed</error>");
            if (!empty($aProcesses))
            {
                $output->writeln("Shutting down jobs in background...", OutputInterface::VERBOSITY_VERBOSE);
                foreach ($aProcesses as $oRunningCommand)
                {
                    $output->writeln("- " . $oRunningCommand->getCommand(), OutputInterface::VERBOSITY_VERBOSE);
                    $oRunningCommand->tearDown();
                }
            }

            throw new \Exception("Stage failed", ReturnCodes::FAILED);
        }
    }
}