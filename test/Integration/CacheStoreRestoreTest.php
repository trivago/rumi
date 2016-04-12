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
class CacheStoreRestoreIntegrationTest extends \PHPUnit_Framework_TestCase
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
        if (strpos(@$_SERVER['COMMAND_MODE'], 'unix') === 0){
            $this->markTestSkipped("flock not supported in unix");
        }
        // given
        $sTempWorkdir = sys_get_temp_dir() . '/runner-integration-' . time();
        mkdir($sTempWorkdir);
        mkdir($sTempWorkdir.'/workdir');
        mkdir($sTempWorkdir.'/workdir2');
        mkdir($sTempWorkdir.'/cache');

        file_put_contents($sTempWorkdir.'/workdir/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'    - .git');
        mkdir($sTempWorkdir.'/workdir/.git');
        touch($sTempWorkdir.'/workdir/.git/test');

        // when

        chdir($sTempWorkdir.'/workdir');
        $oCacheStoreCommand = new CacheStoreCommand($this->container);
        $oCacheStoreCommand->run(
            new ArrayInput(
                [
                    'cache_dir' => $sTempWorkdir . '/cache',
                    'git_repository' => 'a',
                    'git_branch' => 'origin/master'
                ]
            ),
            $this->output
        );
        chdir($sTempWorkdir . '/workdir2');
        $oCacheRestoreCommand = new CacheRestoreCommand($this->container);
        $oCacheRestoreCommand->run(
            new ArrayInput(
                [
                    'cache_dir' => $sTempWorkdir . '/cache',
                    'git_repository' => 'a'
                ]
            ),
            $this->output

        );

        // then
        $this->assertFileEquals(
            $sTempWorkdir . '/workdir/.git/test',
            $sTempWorkdir . '/workdir2/.git/test',
            $this->output->fetch()
        );
    }
}