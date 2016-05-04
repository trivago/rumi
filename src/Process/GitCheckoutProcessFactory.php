<?php
/**
 * @author jsacha
 *
 * @since 23/02/16 08:15
 */

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;

class GitCheckoutProcessFactory
{
    protected $fetchCommand = 'GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin';

    public function getFullCloneProcess($repositoryUrl)
    {
        $process = new Process(
            'git init && git remote add origin '.$repositoryUrl.' && '.$this->fetchCommand
        );
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    public function getFetchProcess()
    {
        $process = new Process($this->fetchCommand);
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    public function getCheckoutCommitProcess($commitSha)
    {
        $process = new Process(
            'git reset --hard && git checkout '.$commitSha
        );
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    public function getMergeProcess($branch)
    {
        $process = new Process(
            'git merge --no-edit '.$branch
        );
        $process->setTimeout(60)->setIdleTimeout(60);

        return $process;
    }
}
