<?php
/**
 * @author jsacha
 * @since 23/02/16 08:30
 */

namespace jakubsacha\Rumi\Commands;


use jakubsacha\Rumi\Process\GitCheckoutProcessFactory;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;

/**
 * @covers jakubsacha\Rumi\Commands\CheckoutCommand
 */
class CheckoutCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var CheckoutCommand
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

        $this->oSUT = new CheckoutCommand(
            $this->container
        );
        $this->oSUT->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenWorkingDirIsEmpty_WhenCommandExecuted_ThenFullCheckoutIsDone()
    {
        // given
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $oFullCloneProcess = $this->prophesize(Process::class);
        $oFullCloneProcess->run()->shouldBeCalled();
        $oFullCloneProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oCheckoutCommitProcess = $this->prophesize(Process::class);
        $oCheckoutCommitProcess->run()->shouldBeCalled();
        $oCheckoutCommitProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oFactory->getFullCloneProcess('abc')->willReturn($oFullCloneProcess->reveal())->shouldBeCalled();
        $oFactory->getCheckoutCommitProcess('sha123')->willReturn($oCheckoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Checkout done', $this->output->fetch());
    }

    public function testGivenWorkingDirContainsDotGit_WhenCommandExecuted_ThenFetchIsDone()
    {
        // given
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory').'/.git');

        $oFetchProcess = $this->prophesize(Process::class);
        $oFetchProcess->run()->shouldBeCalled();
        $oFetchProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oCheckoutCommitProcess = $this->prophesize(Process::class);
        $oCheckoutCommitProcess->run()->shouldBeCalled();
        $oCheckoutCommitProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oFactory->getFetchProcess()->willReturn($oFetchProcess->reveal())->shouldBeCalled();
        $oFactory->getCheckoutCommitProcess('sha123')->willReturn($oCheckoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Checkout done', $this->output->fetch());
    }

    public function testGivenProcessFailing_WhenCommandExecuted_ThenErrorIsDisplayed()
    {
        // given
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $oProcess = $this->prophesize(Process::class);
        $oProcess->run()->shouldBeCalled();
        $oProcess->isSuccessful()->willReturn(false)->shouldBeCalled();
        $oProcess->getErrorOutput()->willReturn('error')->shouldBeCalled();

        $oFactory->getFullCloneProcess('abc')->willReturn($oProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('error', $this->output->fetch());
    }

    public function testGivenMergeIsNotSpecified_WhenCommandExecuted_ThenItMergesWithMaster()
    {
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory').'/.git');

        $oFetchProcess = $this->prophesize(Process::class);
        $oFetchProcess->run()->shouldBeCalled();
        $oFetchProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oCheckoutCommitProcess = $this->prophesize(Process::class);
        $oCheckoutCommitProcess->run()->shouldBeCalled();
        $oCheckoutCommitProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oFactory->getFetchProcess()->willReturn($oFetchProcess->reveal())->shouldBeCalled();
        $oFactory->getCheckoutCommitProcess('sha123')->willReturn($oCheckoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Checkout done', $this->output->fetch());
    }


    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt()
    {
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'merge_branch: abc');

        $oFetchProcess = $this->prophesize(Process::class);
        $oFetchProcess->run()->shouldBeCalled();
        $oFetchProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oCheckoutCommitProcess = $this->prophesize(Process::class);
        $oCheckoutCommitProcess->run()->shouldBeCalled();
        $oCheckoutCommitProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oMergeProcess = $this->prophesize(Process::class);
        $oMergeProcess->run()->shouldBeCalled();
        $oMergeProcess->isSuccessful()->willReturn(true);
        $oFactory->getMergeProcess('abc')->willReturn($oMergeProcess->reveal());

        $oFactory->getFetchProcess()->willReturn($oFetchProcess->reveal())->shouldBeCalled();
        $oFactory->getCheckoutCommitProcess('sha123')->willReturn($oCheckoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Merging with abc', $this->output->fetch());
    }


    public function testGivenMergeBranchIsNotSpecified_WhenCommandExecuted_ThenItDoesNothing()
    {
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, '');

        $oFetchProcess = $this->prophesize(Process::class);
        $oFetchProcess->run()->shouldBeCalled();
        $oFetchProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oCheckoutCommitProcess = $this->prophesize(Process::class);
        $oCheckoutCommitProcess->run()->shouldBeCalled();
        $oCheckoutCommitProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oFactory->getFetchProcess()->willReturn($oFetchProcess->reveal())->shouldBeCalled();
        $oFactory->getCheckoutCommitProcess('sha123')->willReturn($oCheckoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertNotContains('Merging with origin/master', $this->output->fetch());
    }

    public function testGivenMergeFails_WhenCommandExecuted_ThenItReturnsValidOutput()
    {
        /** @var GitCheckoutProcessFactory $oFactory */
        $oFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/' . RunCommand::CONFIG_FILE, 'merge_branch: origin/master');

        $oFetchProcess = $this->prophesize(Process::class);
        $oFetchProcess->run()->shouldBeCalled();
        $oFetchProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oCheckoutCommitProcess = $this->prophesize(Process::class);
        $oCheckoutCommitProcess->run()->shouldBeCalled();
        $oCheckoutCommitProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $oMergeProcess = $this->prophesize(Process::class);
        $oMergeProcess->run()->shouldBeCalled();
        $oMergeProcess->isSuccessful()->willReturn(false);
        $oFactory->getMergeProcess('origin/master')->willReturn($oMergeProcess->reveal());

        $oFactory->getFetchProcess()->willReturn($oFetchProcess->reveal())->shouldBeCalled();
        $oFactory->getCheckoutCommitProcess('sha123')->willReturn($oCheckoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('jakubsacha.rumi.process.git_checkout_process_factory', $oFactory->reveal());


        // when
        $this->oSUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123'
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Can not clearly merge with origin/master', $this->output->fetch());
    }

}
