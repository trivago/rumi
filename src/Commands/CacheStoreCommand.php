<?php

/*
 * Copyright 2016 trivago GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Trivago\Rumi\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Exceptions\SkipException;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Services\ConfigReaderInterface;

class CacheStoreCommand extends CommandAbstract
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
        parent::configure();

        $this
            ->setName('cache:store')
            ->setDescription('Store cache')
            ->addArgument('cache_dir', InputArgument::REQUIRED, 'cache directory')
            ->addArgument('git_repository', InputArgument::REQUIRED, 'repository')
            ->addArgument('git_branch', InputArgument::REQUIRED, 'currently built branch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $ciConfig = $this->getCiConfig();

            $cacheDir = $input->getArgument('cache_dir') . '/' . md5($input->getArgument('git_repository'));

            $this->SkipIfCacheConfigIsEmpty($ciConfig);
            $this->SkipIfDestCacheDirDoesNotExist($input);
            $this->SkipIfNotMasterAndCacheFilled($input->getArgument('git_branch'), $cacheDir);

            $this->createCacheDirectory($cacheDir);

            $cacheStoreDir = $this->container->get('trivago.rumi.commands.cache_store.cache_store_dir');

            foreach ($ciConfig->getCache() as $dir) {
                $output->writeln($cacheStoreDir->store($dir, $cacheDir));
            }

            $output->writeln('<info>Cache store done</info>');
        } catch (SkipException $e) {
            $output->writeln('<info>' . $e->getMessage() . '</info>');

            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return $e->getCode() != 0 ? $e->getCode() : -1;
        }

        return 0;
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
    protected function SkipIfCacheConfigIsEmpty(RunConfig $ciConfig)
    {
        if (!$ciConfig->getCache()->count()) {
            throw new SkipException('Cache config is empty. Skipping.');
        }
    }

    /**
     * @param InputInterface $input
     *
     * @throws SkipException
     */
    protected function SkipIfDestCacheDirDoesNotExist(InputInterface $input)
    {
        if (!file_exists($input->getArgument('cache_dir'))) {
            throw new SkipException('Destination cache directory does not exist. Skipping.');
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

    private function getCiConfig()
    {
        try {
            /** @var ConfigReaderInterface $configReader */
            $configReader = $this->container->get('trivago.rumi.services.config_reader');

            return $configReader->getRunConfig();
        } catch (\Exception $e) {
            throw new \RuntimeException('Required file \'' . ConfigReader::CONFIG_FILE . '\' does not exist', $e->getCode());
        }
    }
}
