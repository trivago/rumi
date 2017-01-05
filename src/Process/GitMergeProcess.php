<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 04/01/17
 * Time: 09:15
 */

namespace Trivago\Rumi\Process;


use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitMergeProcess
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;


    /**
     * GitMergeProcess constructor.
     * @param ConfigReader $configReader
     * @param GitCheckoutProcessFactory $gitCheckoutProcessFactory
     * @param GitCheckoutValidator $gitCheckoutValidator
     */
    public function __construct(
        ConfigReader $configReader,
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        GitCheckoutValidator $gitCheckoutValidator
        )
    {
        $this->configReader = $configReader;
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }


    /**
     * @param $workingDir
     * @param $configFile
     * @return null|string|void
     */
    public function getMergeBranch($workingDir, $configFile)
    {
        try {
            $configReader = $this->configReader;

            $config = $configReader->getRunConfig($workingDir, $configFile);

            if (!empty($config->getMergeBranch())) {
                return $config->getMergeBranch();
            }
        } catch (\Exception $e) {
        }

        return;
    }

    /**
     * @param $workingDir
     * @param $configFile
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    public function executeGitMergeBranchProcess($workingDir, $configFile, OutputInterface $output)
    {
        $mergeBranch = $this->getMergeBranch($workingDir, $configFile);

        if (!empty($mergeBranch)) {
            $output->writeln('Merging with '.$mergeBranch);
            try {
                $process = $this->gitCheckoutProcessFactory->getMergeProcess($mergeBranch);
                $process->run();
                $this->gitCheckoutValidator->checkStatus($process);
            } catch (\Exception $e) {
                throw new \Exception('Can not clearly merge with '.$mergeBranch);
            }
        }
    }
}