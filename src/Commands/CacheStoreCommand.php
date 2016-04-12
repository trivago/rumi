<?php

namespace jakubsacha\Rumi\Commands;

use jakubsacha\Rumi\Exceptions\SkipException;
use jakubsacha\Rumi\Timer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;

class CacheStoreCommand extends Command
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
            ->setName('cache:store')
            ->setDescription('Store cache')
            ->addArgument('cache_dir', InputArgument::REQUIRED, "cache directory")
            ->addArgument('git_repository', InputArgument::REQUIRED, "repository")
            ->addArgument('git_branch', InputArgument::REQUIRED, "currently built branch");
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
        if (empty($this->sWorkingDir))
        {
            return null;
        }
        return $this->sWorkingDir . '/';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            $this->SkipIfConfigFileDoesNotExist();

            $aCiConfig = $this->readCiConfigFile();
            $sCacheDir = $input->getArgument('cache_dir').'/'.md5($input->getArgument('git_repository'));

            $this->SkipIfCacheIsEmpty($aCiConfig);
            $this->SkipIfCacheDirDoesNotExist($input);
            $this->SkipIfNotMasterAndCacheFilled($input->getArgument('git_branch'), $sCacheDir);

            $this->createCacheDirectory($sCacheDir);

            foreach ($aCiConfig['cache'] as $sDir)
            {
                $output->write("Storing cache for: " . $sDir . "... ");

                $oProcess = $this
                    ->oContainer
                    ->get('jakubsacha.rumi.process.cache_process_factory')
                    ->getCacheStoreProcess($sDir, $sCacheDir);

                $sTime = Timer::execute(function() use ($oProcess){
                    $oProcess->run();
                });

                $output->writeln($sTime);

                if (!$oProcess->isSuccessful())
                {
                    throw new Exception($oProcess->getOutput() . $oProcess->getErrorOutput());
                }
            }

            $output->writeln("<info>Cache store done</info>");
        }
        catch (SkipException $e)
        {
            $output->writeln("<info>" . $e->getMessage() . "</info>");
            return 0;
        }
        catch (\Exception $e)
        {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            return -1;
        }
        return 0;
    }

    /**
     * @return mixed
     */
    protected function readCiConfigFile()
    {
        $aParser = new Parser();
        $aCiConfig = $aParser->parse(file_get_contents($this->getWorkingDir().RunCommand::CONFIG_FILE));

        return $aCiConfig;
    }

    /**
     * @param $sCacheDir
     * @return Process
     */
    protected function createCacheDirectory($sCacheDir)
    {
        if (file_exists($sCacheDir . '/data/'))
        {
            return;
        }

        $oProcess = $this
            ->oContainer
            ->get('jakubsacha.rumi.process.cache_process_factory')
            ->getCreateCacheDirectoryProcess($sCacheDir);

        $oProcess->run();
    }

    /**
     * @param $aCiConfig
     * @throws SkipException
     */
    protected function SkipIfCacheIsEmpty($aCiConfig)
    {
        if (empty($aCiConfig['cache']))
        {
            throw new SkipException("Cache config is empty. Skipping.");
        }
    }

    /**
     * @param InputInterface $input
     * @throws SkipException
     */
    protected function SkipIfCacheDirDoesNotExist(InputInterface $input)
    {
        if (!file_exists($input->getArgument('cache_dir')))
        {
            throw new SkipException("Destination cache directory does not exist. Skipping.");
        }
    }

    protected function SkipIfConfigFileDoesNotExist()
    {
        if (!file_exists($this->getWorkingDir().RunCommand::CONFIG_FILE))
        {
            throw new \Exception('Required file \'' . RunCommand::CONFIG_FILE . '\' does not exist');
        }
    }

    /**
     * @param $argument
     * @param $sCacheDir
     * @throws SkipException
     */
    protected function SkipIfNotMasterAndCacheFilled($argument, $sCacheDir)
    {
        if ($argument != 'origin/master' && file_exists($sCacheDir))
        {
            throw new SkipException("Cache is written only for the first build and master branch. Skipping.");
        }
    }
}