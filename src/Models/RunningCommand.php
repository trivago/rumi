<?php
/**
 * @author jsacha
 *
 * @since 12/12/15 23:41
 */

namespace Trivago\Rumi\Models;

use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\RunningProcessesFactory;

class RunningCommand
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $yamlPath;

    /**
     * @var RunningProcessesFactory
     */
    private $runningProcessesFactory;

    /**
     * @var string|null
     */
    private $tempContainerId;

    /**
     * @var JobConfig
     */
    private $jobConfig;

    /**
     * @param JobConfig               $jobConfig
     * @param string                  $yamlPath
     * @param RunningProcessesFactory $factory
     */
    public function __construct(
        JobConfig $jobConfig,
        $yamlPath,
        RunningProcessesFactory $factory
    ) {
        $this->jobConfig = $jobConfig;
        $this->yamlPath = $yamlPath;
        $this->runningProcessesFactory = $factory;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->jobConfig->getCommandsAsString();
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return string
     */
    public function getYamlPath()
    {
        return $this->yamlPath;
    }

    /**
     * Generates tmp name for running CI job.
     *
     * @return string
     */
    private function getTmpName()
    {
        if (empty($this->tempContainerId)) {
            $this->tempContainerId = 'cirunner-'.md5(uniqid().time().$this->getCommand());
        }

        return $this->tempContainerId;
    }

    /**
     */
    public function start()
    {
        $this->process =
            $this->runningProcessesFactory->getJobStartProcess(
                $this->getYamlPath(),
                $this->getTmpName(),
                $this->jobConfig->getCiContainer()
            );

        $this->process->start();
    }

    /**
     * Tears down running process.
     */
    public function tearDown()
    {
        $this
            ->runningProcessesFactory
            ->getTearDownProcess($this->getYamlPath(), $this->getTmpName())
            ->run();
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->process->isRunning();
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->process->getOutput().$this->process->getErrorOutput();
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return $this->jobConfig->getName();
    }
}
