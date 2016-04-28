<?php
/**
 * @author jsacha
 * @since 23/02/16 10:42
 */

namespace jakubsacha\Rumi\Commands;


use jakubsacha\Rumi\Process\CacheProcessFactory;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;

/**
 * @covers jakubsacha\Rumi\Commands\CacheStoreCommand
 */
class CacheStoreCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var CacheStoreCommand
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

        $this->oSUT = new CacheStoreCommand(
            $this->container
        );
        $this->oSUT->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenCiFileDoesNotExist_WhenCacheStoreRun_ThenItSkipsExecution()
    {
        // given

        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'master'
                ]
            ),
            $this->output
        );

        // then
        $this->assertEquals('Required file \'' . RunCommand::CONFIG_FILE . '\' does not exist', trim($this->output->fetch()));
    }

    public function testGivenCiCacheConfigIsEmpty_WhenCacheStoreRun_ThenItSkipsExecution()
    {
        // given
        touch(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE);

        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'master'
                ]
            ),
            $this->output
        );

        // then
        $this->assertEquals('Cache config is empty. Skipping.', trim($this->output->fetch()));
    }

    public function testGivenCacheDirectoryDoesExist_WhenCacheStoreRun_ThenItDoesNotCreateIt()
    {
        // given
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'   - .git');
        mkdir(vfsStream::url('directory') . '/cache');
        mkdir(vfsStream::url('directory') . '/cache/'.md5('abc'));
        mkdir(vfsStream::url('directory') . '/cache/'.md5('abc').'/data');


        /** @var CacheProcessFactory $oFactory */
        $oFactory = $this->prophesize(CacheProcessFactory::class);
        $oFactory->getCreateCacheDirectoryProcess(Argument::any())->shouldNotBeCalled();
        $oFactory->getCacheStoreProcess(".git", vfsStream::url('directory') . '/cache/'.md5('abc'))
            ->willReturn($this->prophesize(Process::class)->reveal());

        $this
            ->container
            ->set('jakubsacha.rumi.process.cache_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'origin/master'
                ]
            ),
            $this->output
        );

        // then

    }

    public function testGivenDestinationCacheDirDoesNotExist_WhenCacheStoreRun_ThenItSkipsExecution()
    {
        // given
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'   - .git');

        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'master'
                ]
            ),
            $this->output
        );

        // then
        $this->assertEquals('Destination cache directory does not exist. Skipping.', trim($this->output->fetch()));
    }

    public function testGivenNotMasterBranchAndCacheExists_WhenCacheStoreRun_ThenItSkips()
    {
        // given
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'   - .git');
        mkdir(vfsStream::url('directory') . '/cache');
        // create cache directory for repository
        mkdir(vfsStream::url('directory') . '/cache/'.md5('abc'));

        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'test'
                ]
            ),
            $this->output
        );

        // then
        $this->assertEquals('Cache is written only for the first build and master branch. Skipping.', trim($this->output->fetch()));
    }

    public function testGivenCiCacheConfigIsCorrect_WhenCacheStoreRunWithOriginMasterBranch_ThenItStoresCache()
    {
        // given
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'   - .git');
        mkdir(vfsStream::url('directory') . '/cache');

        /** @var CacheProcessFactory $oFactory */
        $oFactory = $this->prophesize(CacheProcessFactory::class);
        $sCacheDir = vfsStream::url('directory') . '/cache/' . md5('abc');
        mkdir($sCacheDir);

        $oFactory
            ->getCreateCacheDirectoryProcess($sCacheDir)
            ->willReturn($this->prophesize(Process::class)->reveal())
            ->shouldBeCalled();

        $oCacheStoreProcess = $this->prophesize(Process::class);
        $oCacheStoreProcess
            ->run()
            ->shouldBeCalled();

        $oCacheStoreProcess
            ->isSuccessful()
            ->willReturn(true)
            ->shouldBeCalled();

        $oFactory
            ->getCacheStoreProcess('.git', $sCacheDir)
            ->willReturn($oCacheStoreProcess->reveal())
            ->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.cache_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'origin/master'
                ]
            ),
            $this->output
        );

        // then
    }

    public function testGivenCiCacheConfigIsCorrect_WhenCacheStoreRunWithMasterBranch_ThenItStoresCache()
    {
        // given
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'   - .git');
        mkdir(vfsStream::url('directory') . '/cache');

        /** @var CacheProcessFactory $oFactory */
        $oFactory = $this->prophesize(CacheProcessFactory::class);
        $sCacheDir = vfsStream::url('directory') . '/cache/' . md5('abc');
        mkdir($sCacheDir);

        $oFactory
            ->getCreateCacheDirectoryProcess($sCacheDir)
            ->willReturn($this->prophesize(Process::class)->reveal())
            ->shouldBeCalled();

        $oCacheStoreProcess = $this->prophesize(Process::class);
        $oCacheStoreProcess
            ->run()
            ->shouldBeCalled();

        $oCacheStoreProcess
            ->isSuccessful()
            ->willReturn(true)
            ->shouldBeCalled();

        $oFactory
            ->getCacheStoreProcess('.git', $sCacheDir)
            ->willReturn($oCacheStoreProcess->reveal())
            ->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.cache_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'master'
                ]
            ),
            $this->output
        );

        // then
    }

    public function testGivenCiCacheConfigIsCorrect_WhenCacheStoreFails_ThenItReturnsErrorCode()
    {
        // given
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'cache:'.PHP_EOL.'   - .git');
        mkdir(vfsStream::url('directory') . '/cache');

        /** @var CacheProcessFactory $oFactory */
        $oFactory = $this->prophesize(CacheProcessFactory::class);
        $sCacheDir = vfsStream::url('directory') . '/cache/' . md5('abc');
        $oFactory
            ->getCreateCacheDirectoryProcess($sCacheDir)
            ->willReturn($this->prophesize(Process::class)->reveal())
            ->shouldBeCalled();

        $oCacheStoreProcess = $this->prophesize(Process::class);
        $oCacheStoreProcess
            ->run()
            ->shouldBeCalled();

        $oCacheStoreProcess
            ->isSuccessful()
            ->willReturn(false)
            ->shouldBeCalled();

        $oCacheStoreProcess
            ->getOutput()
            ->willReturn('output')
            ->shouldBeCalled();

        $oCacheStoreProcess
            ->getErrorOutput()
            ->willReturn('error')
            ->shouldBeCalled();

        $oFactory
            ->getCacheStoreProcess('.git', $sCacheDir)
            ->willReturn($oCacheStoreProcess->reveal())
            ->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.cache_process_factory', $oFactory->reveal());


        // when
        $iErrorCode = $this->oSUT->run(
            new ArrayInput(
                [
                    'cache_dir' => vfsStream::url('directory') . '/cache',
                    'git_repository' => 'abc',
                    'git_branch'=> 'master'
                ]
            ),
            $this->output
        );

        // then
        $output = $this->output->fetch();
        $this->assertContains('error', $output);
        $this->assertContains('output', $output);
        $this->assertEquals(-1, $iErrorCode);
    }

}
