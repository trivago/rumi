<?php

namespace Trivago\Rumi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Trivago\Rumi\Exceptions\SkipException;
use Trivago\Rumi\Timer;

class CacheRestoreCommand extends Command
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
            ->setName('cache:restore')
            ->setDescription('Restore cache')
            ->addArgument('cache_dir', InputArgument::REQUIRED, 'cache directory')
            ->addArgument('git_repository', InputArgument::REQUIRED, 'repository');
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
            $cacheDir = $input->getArgument('cache_dir') . '/' . md5($input->getArgument('git_repository')) . '/data/';
            $lockDir = $input->getArgument('cache_dir') . '/' . md5($input->getArgument('git_repository'));

            $this->SkipIfCacheDoesNotExist($cacheDir);
            $this->SkipIfCacheDirIsEmpty($cacheDir);

            $output->writeln('Restoring cache... ');

            $process = $this
                ->container
                ->get('rumi.process.cache_process_factory')
                ->getCacheRestoreProcess($cacheDir, $lockDir);

            $time = Timer::execute(
                function () use ($process) {
                    $process->run();
                }
            );

            $output->writeln($time);

            if (!$process->isSuccessful()) {
                throw new \Exception(
                    '<info>Failed to restore cache</info>' . PHP_EOL .
                    $process->getOutput() . $process->getErrorOutput()
                );
            }
            $output->writeln('<info>Cache restored</info>');
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
     * @param string $cacheDirectory
     *
     * @throws SkipException
     */
    protected function SkipIfCacheDoesNotExist($cacheDirectory)
    {
        if (!file_exists($this->getWorkingDir() . $cacheDirectory)) {
            throw new SkipException('<info>Cache directory does not exist. Nothing to restore.</info>');
        }
    }

    /**
     * @param string $cacheDirectory
     *
     * @throws SkipException
     */
    protected function SkipIfCacheDirIsEmpty($cacheDirectory)
    {
        if (count(scandir($this->getWorkingDir() . $cacheDirectory)) == 2) {
            throw new SkipException('<info>Cache directory is empty. Nothing to restore.</info>');
        }
    }
}
