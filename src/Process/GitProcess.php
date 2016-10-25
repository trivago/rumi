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

class GitProcess extends Process
{
    public function checkStatus()
    {
        try {
            if (parent::isSuccessful()) {
                ReturnCodes::SUCCESS;
            }
        }
        catch (\Exception $e){
            if (parent::getExitCode() == 128 && preg_match("/permission/g", parent::getErrorOutput())) {
                ReturnCodes::VOLUME_MOUNT_FROM_FILESYSTEM;
            } else {
                ReturnCodes::FAILED;
            }
        }
    }
}