<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 21/10/16
 * Time: 16:35.
 */

namespace Trivago\Rumi\Validators;

use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\ReturnCodes;

class GitCheckoutValidator
{
    public function checkStatus(Process $process)
    {
        if ($process->isSuccessful()) {
            return;
        }

        if ($process->getExitCode() == 128 && preg_match('/permission/i', $process->getErrorOutput())) {
            throw new \Exception(
              'Rumi has no permissions to your repository',
               ReturnCodes::FAILED_DUE_TO_REPOSITORY_PERMISSIONS
            );
        }

        throw new \Exception(
            $process->getErrorOutput(),
            ReturnCodes::FAILED
        );
    }
}
