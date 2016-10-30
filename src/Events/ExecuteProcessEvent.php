<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 28/10/16
 * Time: 17:53
 */

namespace Trivago\Rumi\Events;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Timer;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class ExecuteProcessEvent extends Event
{
    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    public function __construct(GitCheckoutValidator $gitCheckoutValidator)
    {
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }

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

    /**
     * @param Process $process
     */
    public function validateGitProcess(Process $process)
    {
        return $this->gitCheckoutValidator->checkStatus($process);
    }
}