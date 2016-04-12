<?php
/**
 * @author jsacha
 * @since 24/02/16 08:26
 */

namespace jakubsacha\Rumi\Commands;


use jakubsacha\Rumi\Process\CacheProcessFactory;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;

/**
 * @covers jakubsacha\Rumi\Commands\CacheRestoreCommand
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
    private $oSUT;

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

        $this->oSUT = new CacheRestoreCommand(
            $this->container
        );
        $this->oSUT->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenTestDirectoryDoesNotExist_WhenCacheRestoreIsExecuted_ItSkipsRestore()
    {
        // given

        // when
        $_iCode = $this->oSUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b'
            ]),
            $this->output
        );

        // then

        $this->assertEquals('Cache directory does not exist. Nothing to restore.', trim($this->output->fetch()));
        $this->assertEquals(0, $_iCode);
    }


    public function testGivenTestDirectoryIsEmpty_WhenCacheRestoreIsExecuted_ItSkipsRestore()
    {
        // given
        mkdir(vfsStream::url('directory').'/a');
        mkdir(vfsStream::url('directory').'/a/'.md5('b'));
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data');

        // when
        $_iCode = $this->oSUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b'
            ]),
            $this->output
        );

        // then

        $this->assertEquals('Cache directory is empty. Nothing to restore.', trim($this->output->fetch()));
        $this->assertEquals(0, $_iCode);
    }



    public function testGivenTestDirectoryContainsData_WhenCacheRestoreIsExecutedAndItFails_ItDisplaysErrorMessage()
    {
        // given
        mkdir(vfsStream::url('directory').'/a');
        mkdir(vfsStream::url('directory').'/a/'.md5('b'));
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data');
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data/dir');

        $oRestoreProcess = $this->prophesize(Process::class);
        $oRestoreProcess->run()->shouldBeCalled();
        $oRestoreProcess->isSuccessful()->willReturn(false);
        $oRestoreProcess->getOutput()->willReturn('output');
        $oRestoreProcess->getErrorOutput()->willReturn('error');

        /** @var CacheProcessFactory $oFactory */
        $oFactory = $this->prophesize(CacheProcessFactory::class);
        $oFactory->getCacheRestoreProcess(
            'a/'.md5('b').'/data/',
            'a/'.md5('b')
        )->willReturn($oRestoreProcess->reveal())
        ->shouldBeCalled();


        $this->container->set('jakubsacha.rumi.process.cache_process_factory', $oFactory->reveal());


        // when
        $_iCode = $this->oSUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b'
            ]),
            $this->output
        );

        // then

        $_sOutput = trim($this->output->fetch());

        $this->assertContains('error', $_sOutput);
        $this->assertContains('output', $_sOutput);
        $this->assertEquals(-1, $_iCode);
    }

   public function testGivenTestDirectoryContainsData_WhenCacheRestoreIsExecutedAndItsSuccessful_ItDisplaysOkMessage()
    {
        // given
        mkdir(vfsStream::url('directory').'/a');
        mkdir(vfsStream::url('directory').'/a/'.md5('b'));
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data');
        mkdir(vfsStream::url('directory').'/a/'.md5('b').'/data/dir');

        $oRestoreProcess = $this->prophesize(Process::class);
        $oRestoreProcess->run()->shouldBeCalled();
        $oRestoreProcess->isSuccessful()->willReturn(true);

        /** @var CacheProcessFactory $oFactory */
        $oFactory = $this->prophesize(CacheProcessFactory::class);
        $oFactory->getCacheRestoreProcess(
            'a/'.md5('b').'/data/',
            'a/'.md5('b')
        )->willReturn($oRestoreProcess->reveal())
        ->shouldBeCalled();


        $this->container->set('jakubsacha.rumi.process.cache_process_factory', $oFactory->reveal());


        // when
        $_iCode = $this->oSUT->run(
            new ArrayInput([
                'cache_dir' => 'a',
                'git_repository' => 'b'
            ]),
            $this->output
        );

        // then

        $_sOutput = trim($this->output->fetch());

        $this->assertContains('Cache restored', $_sOutput);
        $this->assertEquals(0, $_iCode);
    }


}
