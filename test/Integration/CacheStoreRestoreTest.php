<?php

namespace jakubsacha\Rumi\Integration;

use jakubsacha\Rumi\Commands\CacheRestoreCommand;
use jakubsacha\Rumi\Commands\CacheStoreCommand;
use jakubsacha\Rumi\Commands\RunCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @coversNothing
 */
class CacheStoreRestoreTest extends \PHPUnit_Framework_TestCase
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
        $loader->load('../../config/services.xml');
    }

    public function testStoreRestoreWorks()
    {
        if (strpos(@$_SERVER['COMMAND_MODE'], 'unix') === 0) {
            $this->markTestSkipped('flock not supported in unix');
        }
        // given
        $tempWorkDir = sys_get_temp_dir() . '/runner-integration-' . time();
        mkdir($tempWorkDir);
        mkdir($tempWorkDir . '/workdir');
        mkdir($tempWorkDir . '/workdir2');
        mkdir($tempWorkDir . '/cache');

        file_put_contents($tempWorkDir . '/workdir/' . RunCommand::CONFIG_FILE, 'cache:' . PHP_EOL . '    - .git');
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
