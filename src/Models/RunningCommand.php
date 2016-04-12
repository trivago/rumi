<?php
/**
 * @author jsacha
 * @since 12/12/15 23:41
 */

namespace jakubsacha\Rumi\Models;


use jakubsacha\Rumi\Process\RunningProcessesFactory;
use Symfony\Component\Process\Process;

class RunningCommand
{
    /**
     * @var Process
     */
    private $oProcess;

    /**
     * @var string
     */
    private $sYamlPath;

    /**
     * @var RunningProcessesFactory
     */
    private $oFactory;

    /**
     * @var string|null
     */
    private $sTempContainerId;

    /**
     * @var JobConfig
     */
    private $oJobConfig;

    /**
     * @param JobConfig $oJobConfig
     * @param string $sYamlPath
     * @param RunningProcessesFactory $oFactory
     */
    public function __construct(
        JobConfig $oJobConfig,
        $sYamlPath,
        RunningProcessesFactory $oFactory
    )
    {
        $this->oJobConfig = $oJobConfig;
        $this->sYamlPath = $sYamlPath;
        $this->oFactory = $oFactory;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->oJobConfig->getCommandsAsString();
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->oProcess;
    }

    /**
     * @return string
     */
    public function getYamlPath()
    {
        return $this->sYamlPath;
    }

    /**
     * Generates tmp name for running CI job
     *
     * @return string
     */
    private function getTmpName()
    {
        if (empty($this->sTempContainerId))
        {
            $this->sTempContainerId = 'cirunner-'.md5(uniqid().time().$this->getCommand());
        }
        return $this->sTempContainerId;
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->oProcess =
            $this->oFactory->getJobStartProcess(
                $this->getYamlPath(),
                $this->getTmpName(),
                $this->oJobConfig->getCiContainer()
            );

        $this->oProcess->start();
    }


    /**
     * Tears down running process
     */
    public function tearDown()
    {
        $this
            ->oFactory
            ->getTearDownProcess($this->getYamlPath(), $this->getTmpName())
            ->run();
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->oProcess->isRunning();
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->oProcess->getOutput() . $this->oProcess->getErrorOutput();
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return $this->oJobConfig->getName();
    }
}