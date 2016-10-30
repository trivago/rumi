<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 28/10/16
 * Time: 17:40
 */

namespace Trivago\Rumi\Process;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class GitCloneProcessFactory
{
    protected $fetchCommand = 'GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin';

    /**
     * @var string
     */
    private $workingDir;


    /**
     * @codeCoverageIgnore
     */
    private function getWorkingDir()
    {
        if (empty($this->workingDir)) {
            return;
        }

        return $this->workingDir . '/';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Process
     */
    public function GitCloneProcess(InputInterface $input, OutputInterface $output) {

        if (!file_exists($this->getWorkingDir() . '.git')) {
            $output->writeln('Cloning...');
            $process =
                $this->getFullCloneProcess($input->getArgument('repository'));
        } else {
            $output->writeln('Fetching changes...');
            $process =
                $this->getFetchProcess();
        }

        return $process;
    }

    /**
     * @param $repositoryUrl
     *
     * @return Process
     */
    public function getFullCloneProcess($repositoryUrl)
    {
        $process = new Process('git init && git remote add origin '.$repositoryUrl.' && '.$this->fetchCommand);
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    /**
     * @return Process
     */
    public function getFetchProcess()
    {
        $process = new Process($this->fetchCommand);
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }
}