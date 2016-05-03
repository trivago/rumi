<?php

namespace jakubsacha\Rumi\Commands;

use jakubsacha\Rumi\Exceptions\SkipException;
use jakubsacha\Rumi\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CacheRestoreCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $oContainer;

    /**
     * @var string
     */
    private $sWorkingDir = null;

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
            $_sCacheDir = $input->getArgument('cache_dir') . '/' . md5($input->getArgument('git_repository')) . '/data/';
            $_sLockDir = $input->getArgument('cache_dir') . '/' . md5($input->getArgument('git_repository'));

            $this->SkipIfCacheDoesNotExist($_sCacheDir);
            $this->SkipIfCacheDirIsEmpty($_sCacheDir);

            $output->writeln('Restoring cache... ');

            $oProcess = $this
                ->oContainer
                ->get('jakubsacha.rumi.process.cache_process_factory')
                ->getCacheRestoreProcess($_sCacheDir, $_sLockDir);

            $_sTime = Timer::execute(
                function () use ($oProcess) {
                    $oProcess->run();
                }
            );

            $output->writeln($_sTime);

            if (!$oProcess->isSuccessful()) {
                throw new \Exception(
                    '<info>Failed to restore cache</info>' . PHP_EOL .
                    $oProcess->getOutput() . $oProcess->getErrorOutput()
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
     * @param string $_sCacheDir
     *
     * @throws SkipException
     */
    protected function SkipIfCacheDoesNotExist($_sCacheDir)
    {
        if (!file_exists($this->getWorkingDir() . $_sCacheDir)) {
            throw new SkipException('<info>Cache directory does not exist. Nothing to restore.</info>');
        }
    }

    /**
     * @param string $_sCacheDir
     *
     * @throws SkipException
     */
    protected function SkipIfCacheDirIsEmpty($_sCacheDir)
    {
        if (count(scandir($this->getWorkingDir() . $_sCacheDir)) == 2) {
            throw new SkipException('<info>Cache directory is empty. Nothing to restore.</info>');
        }
    }
}
