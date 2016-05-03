<?php

namespace jakubsacha\Rumi\Commands;

use jakubsacha\Rumi\Process\GitCheckoutProcessFactory;
use jakubsacha\Rumi\Timer;
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
    private $oContainer;
    /**
     * @var string
     */
    private $sWorkingDir;

    /**
     * RunCommand constructor.
     *
     * @param ContainerInterface $oContainer
     */
    public function __construct(ContainerInterface $oContainer)
    {
        parent::__construct();
        $this->oContainer = $oContainer;
    }

    protected function configure()
    {
        $this
            ->setName('checkout')
            ->setDescription('Checkout code')
            ->addArgument('repository', InputArgument::REQUIRED, 'Repository url')
            ->addArgument('commit', InputArgument::REQUIRED, 'Commit id/branch name to checkout');

        $this->sWorkingDir = getcwd();
    }

    /**
     * @param $dir
     */
    public function setWorkingDir($dir)
    {
        $this->sWorkingDir = $dir;
    }

    /**
     * @codeCoverageIgnore
     */
    private function getWorkingDir()
    {
        if (empty($this->sWorkingDir)) {
            return;
        }

        return $this->sWorkingDir . '/';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var GitCheckoutProcessFactory $_oProcessFactory */
            $_oProcessFactory = $this
                ->oContainer
                ->get('jakubsacha.rumi.process.git_checkout_process_factory');

            if (!file_exists($this->getWorkingDir() . '.git')) {
                $output->writeln('Cloning...');
                $_oProcess =
                    $_oProcessFactory->getFullCloneProcess($input->getArgument('repository'));
            } else {
                $output->writeln('Fetching changes...');
                $_oProcess =
                    $_oProcessFactory->getFetchProcess();
            }

            $output->writeln($this->executeProcess($_oProcess));

            $output->writeln('Checking out ' . $input->getArgument('commit') . ' ');
            $_oProcess = $_oProcessFactory->getCheckoutCommitProcess($input->getArgument('commit'));

            $output->writeln($this->executeProcess($_oProcess));

            $_sMergeBranch = $this->getMergeBranch();
            if (!empty($_sMergeBranch)) {
                $output->writeln('Merging with ' . $_sMergeBranch);
                try {
                    $this->executeProcess($_oProcessFactory->getMergeProcess($_sMergeBranch));
                } catch (\Exception $e) {
                    throw new \Exception('Can not clearly merge with ' . $_sMergeBranch);
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
     * @param $_oProcess
     *
     * @return string
     */
    protected function executeProcess(Process $_oProcess)
    {
        $_iTime = Timer::execute(
            function () use ($_oProcess) {
                $_oProcess->run();

                if (!$_oProcess->isSuccessful()) {
                    throw new \Exception($_oProcess->getErrorOutput());
                }
            }
        );

        return $_iTime;
    }

    private function getMergeBranch()
    {
        try {
            $oConfig = $this->readCiConfigFile();

            if (isset($oConfig['merge_branch'])) {
                return $oConfig['merge_branch'];
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
        $aParser = new Parser();

        return $aParser->parse(file_get_contents($this->getWorkingDir() . RunCommand::CONFIG_FILE));
    }
}
