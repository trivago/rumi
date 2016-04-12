<?php
/**
 * @author jsacha
 * @since 23/02/16 08:15
 */

namespace jakubsacha\Rumi\Process;


use Symfony\Component\Process\Process;

class GitCheckoutProcessFactory
{
    public function getFullCloneProcess($_sRepositoryUrl)
    {
        $_oProcess = new Process(
            "git init && git remote add origin " . $_sRepositoryUrl . " && git fetch origin"
        );
        $_oProcess->setTimeout(600)->setIdleTimeout(600);

        return $_oProcess;
    }

    public function getFetchProcess()
    {
        $_oProcess = new Process(
            "git fetch origin"
        );
        $_oProcess->setTimeout(600)->setIdleTimeout(600);

        return $_oProcess;
    }

    public function getCheckoutCommitProcess($sCommitSha)
    {
        $_oProcess = new Process(
            "git reset --hard && git checkout " . $sCommitSha
        );
        $_oProcess->setTimeout(600)->setIdleTimeout(600);
        return $_oProcess;
    }

    public function getMergeProcess($sBranch)
    {
        $_oProcess = new Process(
            "git merge --no-edit " . $sBranch
        );
        $_oProcess->setTimeout(60)->setIdleTimeout(60);
        return $_oProcess;
    }
}