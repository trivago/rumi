<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 21/10/16
 * Time: 16:35
 */

namespace Trivago\Rumi\Process;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\ReturnCodes;

class GitProcess extends Process
{
    public function checkStatus()
    {
        if (parent::getExitCode() == 128 && preg_match("/permission/g", parent::getErrorOutput())) {
            ReturnCodes::VOLUME_MOUNT_FROM_FILESYSTEM;
        } else {
            ReturnCodes::FAILED;
        }
    }

    public function run() {
        return $this->run();
    }

    public function isSuccessful()
    {
        return parent::isSuccessful();
    }

    public function getErrorOutput()
    {
        return parent::getErrorOutput();
    }
}