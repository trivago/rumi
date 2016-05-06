<?php

namespace Trivago\Rumi\Commands;

use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;

class CheckoutCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $workingDir;

    /**
     * RunCommand constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setName('checkout')
            ->setDescription('Checkout code')
            ->addArgument('repository', InputArgument::REQUIRED, 'Repository url')
            ->addArgument('commit', InputArgument::REQUIRED, 'Commit id/branch name to checkout');

        $this->workingDir = getcwd();
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var GitCheckoutProcessFactory $processFactory */
            $processFactory = $this
                ->container
                ->get('trivago.rumi.process.git_checkout_process_factory');

            if (!file_exists($this->getWorkingDir() . '.git')) {
                $output->writeln('Cloning...');
                $process =
                    $processFactory->getFullCloneProcess($input->getArgument('repository'));
            } else {
                $output->writeln('Fetching changes...');
                $process =
                    $processFactory->getFetchProcess();
            }

            $output->writeln($this->executeProcess($process));

            $output->writeln('Checking out ' . $input->getArgument('commit') . ' ');
            $process = $processFactory->getCheckoutCommitProcess($input->getArgument('commit'));

            $output->writeln($this->executeProcess($process));

            $mergeBranch = $this->getMergeBranch();
            if (!empty($mergeBranch)) {
                $output->writeln('Merging with ' . $mergeBranch);
                try {
                    $this->executeProcess($processFactory->getMergeProcess($mergeBranch));
                } catch (\Exception $e) {
                    throw new \Exception('Can not clearly merge with ' . $mergeBranch);
                }
            }

            $output->writeln('<info>Checkout done</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return -1;
        }

        return 0;
    }

    /**
     * @param $process
     *
     * @return string
     */
    protected function executeProcess(Process $process)
    {
        $time = Timer::execute(
            function () use ($process) {
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new \Exception($process->getErrorOutput());
                }
            }
        );

        return $time;
    }

    private function getMergeBranch()
    {
        try {
            $config = $this->readCiConfigFile();

            if (isset($config['merge_branch'])) {
                return $config['merge_branch'];
            }
        } catch (\Exception $e) {
        }

        return;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function readCiConfigFile()
    {
        if (!file_exists($this->getWorkingDir() . RunCommand::CONFIG_FILE)) {
            throw new \Exception('Required file \'' . RunCommand::CONFIG_FILE . '\' does not exist');
        }
        $parser = new Parser();

        return $parser->parse(file_get_contents($this->getWorkingDir() . RunCommand::CONFIG_FILE));
    }
}
