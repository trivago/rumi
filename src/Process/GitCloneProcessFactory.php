<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 28/10/16
 * Time: 17:40
 */

namespace Trivago\Rumi\Process;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trivago\Rumi\Commands\CommandAbstract;

class GitCloneProcessFactory
{
    /**
     * @var GitCheckoutProcessFactory
     */
    private $gitCheckoutProcessFactory;

    /**
     * @var string
     */
    private $workingDir;

    public function __construct(GitCheckoutProcessFactory $gitCheckoutProcessFactory)
    {
        $this->gitCheckoutProcessFactory = $gitCheckoutProcessFactory;
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

    public function GitCloneProcess(InputInterface $input, OutputInterface $output) {

        $processFactory = $this->gitCheckoutProcessFactory;

        if (!file_exists($this->getWorkingDir() . '.git')) {
            $output->writeln('Cloning...');
            $process =
                $processFactory->getFullCloneProcess($input->getArgument('repository'));
        } else {
            $output->writeln('Fetching changes...');
            $process =
                $processFactory->getFetchProcess();
        }

        return $process;
    }
}