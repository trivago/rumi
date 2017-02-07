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

namespace Trivago\Rumi\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Trivago\Rumi\Commands\CacheRestoreCommand;
use Trivago\Rumi\Commands\CacheStoreCommand;
use Trivago\Rumi\Services\ConfigReader;

/**
 * @coversNothing
 */
class CacheStoreRestoreTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var BufferedOutput
     */
    private $output;

    public function setUp()
    {
        $this->output = new BufferedOutput();

        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('../../src/Resources/config/services.xml');
    }

    public function testStoreRestoreWorks()
    {
        if (exec('uname') == 'Darwin') {
            $this->markTestSkipped('flock not supported in unix');
        }
        // given
        $tempWorkDir = sys_get_temp_dir() . '/runner-integration-' . time();
        mkdir($tempWorkDir);
        mkdir($tempWorkDir . '/workdir');
        mkdir($tempWorkDir . '/workdir2');
        mkdir($tempWorkDir . '/cache');

        file_put_contents($tempWorkDir . '/workdir/' . ConfigReader::CONFIG_FILE, 'cache:' . PHP_EOL . '    - .git');
        mkdir($tempWorkDir . '/workdir/.git');
        touch($tempWorkDir . '/workdir/.git/test');

        // when

        chdir($tempWorkDir . '/workdir');
        $cacheStoreCommand = new CacheStoreCommand($this->container);
        $cacheStoreCommand->run(
            new ArrayInput(
                [
                    'cache_dir' => $tempWorkDir . '/cache',
                    'git_repository' => 'a',
                    'git_branch' => 'origin/master',
                ]
            ),
            $this->output
        );
        chdir($tempWorkDir . '/workdir2');
        $cacheRestoreCommand = new CacheRestoreCommand($this->container);
        $cacheRestoreCommand->run(
            new ArrayInput(
                [
                    'cache_dir' => $tempWorkDir . '/cache',
                    'git_repository' => 'a',
                ]
            ),
            $this->output

        );

        // then
        $this->assertFileEquals(
            $tempWorkDir . '/workdir/.git/test',
            $tempWorkDir . '/workdir2/.git/test',
            $this->output->fetch()
        );
    }
}
