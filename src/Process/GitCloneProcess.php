<?php
namespace Trivago\Rumi\Process;


use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Resources\WorkingDir;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitCloneProcess
{
    /**
     * @var WorkingDir
     */
    private $workingDir;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;



    public function __construct(
        WorkingDir $workingDir,
        GitCheckoutProcessFactory $gitCheckoutProcessFactory,
        GitCheckoutValidator $gitCheckoutValidator
    )
    {
        $this->workingDir = $workingDir;
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
        $this->gitCheckoutValidator = $gitCheckoutValidator;
    }

    /**
     * @param $repositoryUrl
     * @param OutputInterface $output
     */
    public function executeGitCloneBranch($repositoryUrl, OutputInterface $output)
    {
        if (!file_exists($this->workingDir->getWorkingDir().'.git')) {
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