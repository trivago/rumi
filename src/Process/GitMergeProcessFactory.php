<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 30/10/16
 * Time: 16:51
 */

namespace Trivago\Rumi\Process;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\CommandAbstract;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitMergeProcessFactory extends CommandAbstract
{
    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * GitMergeProcessFactory constructor.
     * @param GitCheckoutValidator $gitCheckoutValidator
     * @param ConfigReader $configReader
     */
    public function __construct(GitCheckoutValidator $gitCheckoutValidator, ConfigReader $configReader)
    {
        parent::__construct();
        $this->gitCheckoutValidator = $gitCheckoutValidator;
        $this->configReader = $configReader;
    }

    public function mergeBranchProcess(InputInterface $input, OutputInterface $output)
    {
        $mergeBranch = $this->getMergeBranch($input->getOption(self::CONFIG));
        if (!empty($mergeBranch)) {
            $process = $this->getMergeProcess($mergeBranch);
            $this->gitCheckoutValidator->validateMergeBranchProcess($mergeBranch, $output, $process);
            return $process;
        }
        return;
    }

    private function getMergeBranch($configFile)
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

    /**
     * @codeCoverageIgnore
     */
    private function getWorkingDir()
    {
        if (empty($this->workingDir)) {
            return "The working directory is empty";
        }

        return $this->workingDir . '/';
    }

    /**
     * @param $branch
     *
     * @return Process
     */
    public function getMergeProcess($branch)
    {
        $process = new Process('git merge --no-edit '.$branch);
        $process->setTimeout(60)->setIdleTimeout(60);

        return $process;
    }
}