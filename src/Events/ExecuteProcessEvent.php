<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 28/10/16
 * Time: 17:53
 */

namespace Trivago\Rumi\Events;


use Symfony\Component\Process\Process;
use Trivago\Rumi\Timer;

class ExecuteProcessEvent
{
    /**
     * @param $process
     *
     * @return string
     */
    public function executeProcess(Process $process)
    {
        $time = Timer::execute(
            function () use ($process) {
                $process->run();
            }
        );

        return $time;
    }
}