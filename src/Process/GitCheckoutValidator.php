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

class GitCheckoutValidator
{
    public function checkStatus(Process $process)
    {
        if ($process->isSuccessful()) {
            return;
        }

        if ($process->getExitCode() == 128 && preg_match("/permission/", $process->getErrorOutput())) {
            throw new \Exception(
              'Your repository is not public. Please check permissions',
               ReturnCodes::FAILED_DUE_TO_REPOSITORY_PERMISSIONS
            );
        }

        throw new \Exception(
            $process->getErrorOutput(),
            ReturnCodes::FAILED
        );
    }
}