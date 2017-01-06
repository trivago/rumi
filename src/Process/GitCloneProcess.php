<?php
namespace Trivago\Rumi\Process;


use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitCloneProcess
{
    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;



    public function __construct(
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        GitCheckoutValidator $gitCheckoutValidator
    )
    {
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }

    /**
     * @param $workingDir
     * @param $repositoryUrl
     * @param OutputInterface $output
     */
    public function executeGitCloneBranch($workingDir, $repositoryUrl, OutputInterface $output)
    {
        if (!file_exists($workingDir.'.git')) {
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
}