<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 01/11/16
 * Time: 11:55
 */

namespace Trivago\Rumi\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitCheckoutExecuteCommands
{
    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var string
     */
    private $workingDir;

    public function __construct(
        GitCheckoutValidator $gitCheckoutValidator,
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        ConfigReader $configReader)
    {
        $this->gitCheckoutValidator = $gitCheckoutValidator;
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->configReader = $configReader;
    }

    /**
     * @param $dir
     */
    public function setWorkingDir($dir)
    {
        $this->workingDir = $dir;
    }

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
     * @param $repositoryUrl
     * @param OutputInterface $output
     */
    public function executeGitCloneBranch($repositoryUrl, OutputInterface $output) {
        if (!file_exists($this->getWorkingDir() . '.git'))
        {
            $output->writeln('Cloning...');
            $process =
                $this->gitCheckoutProcessFactory->getFullCloneProcess($repositoryUrl);
        } else {
            $output->writeln('Fetching changes...');
            $process =
                $this->gitCheckoutProcessFactory->getFetchProcess();
        }

        $process->run();
        $this->gitCheckoutValidator->checkStatus($process);

    }

    public function getMergeBranch($configFile)
    {
        try {
            $configReader = $this->configReader;

            $config = $configReader->getConfig($this->getWorkingDir(), $configFile);

            if (!empty($config->getMergeBranch())) {
                return $config->getMergeBranch();
            }
        } catch (\Exception $e) {
        }

        return;
    }

    public function executeGitMergeBranchProcess($configFile, OutputInterface $output) {
        $mergeBranch = $this->getMergeBranch($configFile);

        if (!empty($mergeBranch)) {
            $output->writeln('Merging with ' . $mergeBranch);
            try {
                $process = $this->gitCheckoutProcessFactory->getMergeProcess($mergeBranch);
                $process->run();
                $this->gitCheckoutValidator->checkStatus($process);
            } catch (\Exception $e) {
                throw new \Exception('Can not clearly merge with ' . $mergeBranch);
            }
        }
    }

    public function executeGitCheckoutCommitProcess ($commitSha, OutputInterface $output)
    {
        $output->writeln('Checking out ' . $commitSha . ' ');
        $process = $this->gitCheckoutProcessFactory->getCheckoutCommitProcess($commitSha);

        $process->run();
        $this->gitCheckoutValidator->checkStatus($process);

    }

}