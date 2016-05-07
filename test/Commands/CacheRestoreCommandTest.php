<?php
/**
 * @author jsacha
 *
 * @since 24/02/16 08:26
 */

namespace Trivago\Rumi\Commands;

use Trivago\Rumi\Process\CacheProcessFactory;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;

/**
 * @covers Trivago\Rumi\Commands\CacheRestoreCommand
 */
class CacheRestoreCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var CacheRestoreCommand
     */
    private $SUT;

    /**
     * @var BufferedOutput
     */
    private $output;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('../../config/services.xml');

        $this->SUT = new CacheRestoreCommand(
            $this->container
        );
        $this->SUT->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenTestDirectoryDoesNotExist_WhenCacheRestoreIsExecuted_ItSkipsRestore()
    {
        // given

        // when
        $returnCode = $this->SUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b',
            ]),
            $this->output
        );

        // then

        $this->assertEquals('Cache directory does not exist. Nothing to restore.', trim($this->output->fetch()));
        $this->assertEquals(0, $returnCode);
    }

    public function testGivenTestDirectoryIsEmpty_WhenCacheRestoreIsExecuted_ItSkipsRestore()
    {
        // given
        mkdir(vfsStream::url('directory').'/a');
        mkdir(vfsStream::url('directory').'/a/'.md5('b'));
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data');

        // when
        $returnCode = $this->SUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b',
            ]),
            $this->output
        );

        // then

        $this->assertEquals('Cache directory is empty. Nothing to restore.', trim($this->output->fetch()));
        $this->assertEquals(0, $returnCode);
    }

    public function testGivenTestDirectoryContainsData_WhenCacheRestoreIsExecutedAndItFails_ItDisplaysErrorMessage()
    {
        // given
        mkdir(vfsStream::url('directory').'/a');
        mkdir(vfsStream::url('directory').'/a/'.md5('b'));
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data');
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data/dir');

        $restoreProcess = $this->prophesize(Process::class);
        $restoreProcess->run()->shouldBeCalled();
        $restoreProcess->isSuccessful()->willReturn(false);
        $restoreProcess->getOutput()->willReturn('output');
        $restoreProcess->getErrorOutput()->willReturn('error');

        /** @var CacheProcessFactory $factory */
        $factory = $this->prophesize(CacheProcessFactory::class);
        $factory->getCacheRestoreProcess(
            'a/'.md5('b').'/data/',
            'a/'.md5('b')
        )->willReturn($restoreProcess->reveal())
        ->shouldBeCalled();

        $this->container->set('trivago.rumi.process.cache_process_factory', $factory->reveal());

        // when
        $returnCode = $this->SUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b',
            ]),
            $this->output
        );

        // then

        $output = trim($this->output->fetch());

        $this->assertContains('error', $output);
        $this->assertContains('output', $output);
        $this->assertEquals(-1, $returnCode);
    }

    public function testGivenTestDirectoryContainsData_WhenCacheRestoreIsExecutedAndItsSuccessful_ItDisplaysOkMessage()
    {
        // given
        mkdir(vfsStream::url('directory').'/a');
        mkdir(vfsStream::url('directory').'/a/'.md5('b'));
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data');
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data/dir');

        $restoreProcess = $this->prophesize(Process::class);
        $restoreProcess->run()->shouldBeCalled();
        $restoreProcess->isSuccessful()->willReturn(true);

        /** @var CacheProcessFactory $factory */
        $factory = $this->prophesize(CacheProcessFactory::class);
        $factory->getCacheRestoreProcess(
            'a/'.md5('b').'/data/',
            'a/'.md5('b')
        )->willReturn($restoreProcess->reveal())
        ->shouldBeCalled();

        $this->container->set('trivago.rumi.process.cache_process_factory', $factory->reveal());

        // when
        $code = $this->SUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b',
            ]),
            $this->output
        );

        // then

        $output = trim($this->output->fetch());

        $this->assertContains('Cache restored', $output);
        $this->assertEquals(0, $code);
    }
}
