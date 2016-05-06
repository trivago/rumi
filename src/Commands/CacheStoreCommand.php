<?php

namespace Trivago\Rumi\Commands;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;
use Trivago\Rumi\Exceptions\SkipException;
use Trivago\Rumi\Timer;

class CacheStoreCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $workingDir = null;

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
            ->setName('cache:store')
            ->setDescription('Store cache')
            ->addArgument('cache_dir', InputArgument::REQUIRED, 'cache directory')
            ->addArgument('git_repository', InputArgument::REQUIRED, 'repository')
            ->addArgument('git_branch', InputArgument::REQUIRED, 'currently built branch');
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
            $this->SkipIfConfigFileDoesNotExist();

            $ciConfig = $this->readCiConfigFile();
            $cacheDir = $input->getArgument('cache_dir') . '/' . md5($input->getArgument('git_repository'));

            $this->SkipIfCacheIsEmpty($ciConfig);
            $this->SkipIfCacheDirDoesNotExist($input);
            $this->SkipIfNotMasterAndCacheFilled($input->getArgument('git_branch'), $cacheDir);

            $this->createCacheDirectory($cacheDir);

            foreach ($ciConfig['cache'] as $dir) {
                $output->write('Storing cache for: ' . $dir . '... ');

                $process = $this
                    ->container
                    ->get('trivago.rumi.process.cache_process_factory')
                    ->getCacheStoreProcess($dir, $cacheDir);

                $time = Timer::execute(function () use ($process) {
                    $process->run();
                });

                $output->writeln($time);

                if (!$process->isSuccessful()) {
                    throw new Exception($process->getOutput() . $process->getErrorOutput());
                }
            }

            $output->writeln('<info>Cache store done</info>');
        } catch (SkipException $e) {
            $output->writeln('<info>' . $e->getMessage() . '</info>');

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return -1;
        }

        return 0;
    }

    /**
     * @return mixed
     */
    protected function readCiConfigFile()
    {
        $parser = new Parser();

        return $parser->parse(file_get_contents($this->getWorkingDir() . RunCommand::CONFIG_FILE));
    }

    /**
     * @param $cacheDir
     *
     * @return Process
     */
    protected function createCacheDirectory($cacheDir)
    {
        if (file_exists($cacheDir . '/data/')) {
            return;
        }

        $process = $this
            ->container
            ->get('trivago.rumi.process.cache_process_factory')
            ->getCreateCacheDirectoryProcess($cacheDir);

        $process->run();
    }

    /**
     * @param $ciConfig
     *
     * @throws SkipException
     */
    protected function SkipIfCacheIsEmpty($ciConfig)
    {
        if (empty($ciConfig['cache'])) {
            throw new SkipException('Cache config is empty. Skipping.');
        }
    }

    /**
     * @param InputInterface $input
     *
     * @throws SkipException
     */
    protected function SkipIfCacheDirDoesNotExist(InputInterface $input)
    {
        if (!file_exists($input->getArgument('cache_dir'))) {
            throw new SkipException('Destination cache directory does not exist. Skipping.');
        }
    }

    protected function SkipIfConfigFileDoesNotExist()
    {
        if (!file_exists($this->getWorkingDir() . RunCommand::CONFIG_FILE)) {
            throw new \Exception('Required file \'' . RunCommand::CONFIG_FILE . '\' does not exist');
        }
    }

    /**
     * @param $argument
     * @param $cacheDir
     *
     * @throws SkipException
     */
    protected function SkipIfNotMasterAndCacheFilled($argument, $cacheDir)
    {
        if ($argument == 'origin/master' || $argument == 'master') {
            return;
        }

        if (!file_exists($cacheDir)) {
            return;
        }

        throw new SkipException('Cache is written only for the first build and master branch. Skipping.');
    }
}
