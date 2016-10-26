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

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Process\GitProcess;

/**
 * @covers Trivago\Rumi\Commands\CheckoutCommand
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
        $loader->load('../../src/Resources/config/services.xml');

        $this->SUT = new CheckoutCommand(
            $this->container
        );
        $this->SUT->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenWorkingDirIsEmpty_WhenCommandExecuted_ThenFullCheckoutIsDone()
    {
        // given
        /** @var GitCheckoutProcessFactory $processFactory */
        $processFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $fullCloneProcess = $this->prophesize(GitProcess::class);
        $fullCloneProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $fullCloneProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();


        $checkoutCommitProcess = $this->prophesize(GitProcess::class);
        $checkoutCommitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $checkoutCommitProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $processFactory->getFullCloneProcess('abc')->willReturn($fullCloneProcess->reveal())->shouldBeCalled();
        $processFactory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $processFactory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
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
        /** @var GitCheckoutProcessFactory $processFactory */
        $processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory') . '/.git');

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $fetchProcess = $this->prophesize(GitProcess::class);
        $fetchProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $fetchProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();


        $checkoutCommitProcess = $this->prophesize(GitProcess::class);
        $checkoutCommitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $checkoutCommitProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $processFactory->getFetchProcess()->willReturn($fetchProcess->reveal())->shouldBeCalled();
        $processFactory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $processFactory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
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
        /** @var GitCheckoutProcessFactory $factory */
        $factory = $this->prophesize(GitCheckoutProcessFactory::class);

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $gitProcess = $this->prophesize(GitProcess::class);
        $gitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $gitProcess->checkStatus()->willReturn(ReturnCodes::FAILED)->shouldBeCalled();

        $symfonyProcess->getErrorOutput()->willReturn('error')->shouldBeCalled();

        $factory->getFullCloneProcess('abc')->willReturn($gitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $factory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('error', $this->output->fetch());
    }

    public function testGivenMergeIsNotSpecified_WhenCommandExecuted_ThenItMergesWithMaster()
    {
        /** @var GitCheckoutProcessFactory $processFactory */
        $processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory') . '/.git');

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $fetchProcess = $this->prophesize(GitProcess::class);
        $fetchProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $fetchProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();


        $checkoutCommitProcess = $this->prophesize(GitProcess::class);
        $checkoutCommitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $checkoutCommitProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $processFactory->getFetchProcess()->willReturn($fetchProcess->reveal())->shouldBeCalled();
        $processFactory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $processFactory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Checkout done', $this->output->fetch());
    }

    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt()
    {
        /** @var GitCheckoutProcessFactory $factory */
        $factory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory') . '/.git');
        file_put_contents(vfsStream::url('directory') . '/' . CommandAbstract::DEFAULT_CONFIG, 'merge_branch: abc');

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $fetchProcess = $this->prophesize(GitProcess::class);
        $fetchProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $fetchProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $checkoutCommitProcess = $this->prophesize(GitProcess::class);
        $checkoutCommitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $checkoutCommitProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $mergeProcess = $this->prophesize(GitProcess::class);
        $mergeProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS);

        $factory->getMergeProcess('abc')->willReturn($mergeProcess->reveal());

        $factory->getFetchProcess()->willReturn($fetchProcess->reveal())->shouldBeCalled();
        $factory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $factory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Merging with abc', $this->output->fetch());
    }

    public function testGivenMergeBranchIsNotSpecified_WhenCommandExecuted_ThenItDoesNothing()
    {
        /** @var GitCheckoutProcessFactory $factory */
        $factory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory') . '/.git');
        file_put_contents(vfsStream::url('directory') . '/' . CommandAbstract::DEFAULT_CONFIG, '');

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $fetchProcess = $this->prophesize(GitProcess::class);
        $fetchProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $fetchProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $checkoutCommitProcess = $this->prophesize(GitProcess::class);
        $checkoutCommitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $checkoutCommitProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $factory->getFetchProcess()->willReturn($fetchProcess->reveal())->shouldBeCalled();
        $factory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $factory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        // then
        $this->assertNotContains('Merging with origin/master', $this->output->fetch());
    }

    public function testGivenMergeFails_WhenCommandExecuted_ThenItReturnsValidOutput()
    {
        /** @var GitCheckoutProcessFactory $factory */
        $factory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory') . '/.git');
        file_put_contents(vfsStream::url('directory') . '/' . CommandAbstract::DEFAULT_CONFIG, 'merge_branch: origin/master');

        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->run()->shouldBeCalled();

        $fetchProcess = $this->prophesize(GitProcess::class);
        $fetchProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $fetchProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $checkoutCommitProcess = $this->prophesize(GitProcess::class);
        $checkoutCommitProcess->processFunctions()->willReturn($symfonyProcess->reveal());
        $checkoutCommitProcess->checkStatus()->willReturn(ReturnCodes::SUCCESS)->shouldBeCalled();

        $mergeProcess = $this->prophesize(GitProcess::class);
        $mergeProcess->checkStatus()->willReturn(ReturnCodes::FAILED);

        $factory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $factory->getFetchProcess()->willReturn($fetchProcess->reveal())->shouldBeCalled();
        $factory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal())->shouldBeCalled();

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $factory->reveal());

        // when
        $this->SUT->run(
            new ArrayInput(
                [
                    'repository' => 'abc',
                    'commit' => 'sha123',
                ]
            ),
            $this->output
        );

        // then
        $this->assertContains('Can not clearly merge with origin/master', $this->output->fetch());
    }
}
