<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 21/10/16
 * Time: 16:35
 */

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\ReturnCodes;

class GitProcess
{
    /**
     * @var Process
     */
    private $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function processFunctions()
    {
        return $this->process;
    }

    public function checkStatus()
    {
        if ($this->process->isSuccessful()) {
            return ReturnCodes::SUCCESS;
        } elseif ($this->process->getExitCode() == 128 && preg_match("/permission/", $this->process->getErrorOutput())) {
            throw new \Exception(
              'Your repository is not public. Please check permissions',
               ReturnCodes::FAILED_DUE_TO_REPOSITORY_PERMISSIONS
            );
        } else {
            throw new \Exception(
                $this->process->getErrorOutput(),
                 ReturnCodes::FAILED
            );
        }
    }
}