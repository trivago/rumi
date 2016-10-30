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
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Validators\GitCheckoutValidator;

/**
 * @covers \Trivago\Rumi\Commands\CheckoutCommand
 */
class CheckoutCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var GitCheckoutValidator|ObjectProphecy
     */
    private $gitCheckoutValidator;

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

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('../../src/Resources/config/services.xml');

        $this->SUT = new CheckoutCommand(
            $this->container,
            $this->gitCheckoutValidator->reveal()
        );
        $this->SUT->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenWorkingDirIsEmpty_WhenCommandExecuted_ThenFullCheckoutIsDone()
    {
        // given
        /** @var GitCheckoutProcessFactory $processFactory */
        $processFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $fullCloneProcess = $this->prophesize(Process::class);
        $fullCloneProcess->run()->shouldBeCalled();
        $fullCloneProcess->isSuccessful()->willReturn(true);

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run()->shouldBeCalled();

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
        touch(vfsStream::url('directory').'/.git');

        $fetchProcess = $this->prophesize(Process::class);
        $fetchProcess->run()->shouldBeCalled();
        $fetchProcess->isSuccessful()->willReturn(true);

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run()->shouldBeCalled();

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

    public function testGivenMergeIsNotSpecified_WhenCommandExecuted_ThenItMergesWithMaster()
    {
        /** @var GitCheckoutProcessFactory $processFactory */
        $processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        touch(vfsStream::url('directory').'/.git');

        $fetchProcess = $this->prophesize(Process::class);
        $fetchProcess->run()->shouldBeCalled();
        $fetchProcess->isSuccessful()->willReturn(true);

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run()->shouldBeCalled();

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
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/'.CommandAbstract::DEFAULT_CONFIG, 'merge_branch: abc');

        $fetchProcess = $this->prophesize(Process::class);
        $fetchProcess->run()->shouldBeCalled();
        $fetchProcess->isSuccessful()->willReturn(true);

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run()->shouldBeCalled();

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();

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
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/'.CommandAbstract::DEFAULT_CONFIG, '');

        $fetchProcess = $this->prophesize(Process::class);
        $fetchProcess->run()->shouldBeCalled();
        $fetchProcess->isSuccessful()->willReturn(true);

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run()->shouldBeCalled();

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
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/'.CommandAbstract::DEFAULT_CONFIG, 'merge_branch: origin/master');

        $fetchProcess = $this->prophesize(Process::class);
        $fetchProcess->run()->shouldBeCalled();
        $this->gitCheckoutValidator->checkStatus($fetchProcess->reveal())->shouldBeCalled();

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run()->shouldBeCalled();
        $this->gitCheckoutValidator->checkStatus($checkoutCommitProcess->reveal())->shouldBeCalled();

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();
        $mergeProcess->isSuccessful()->willReturn(false);
        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal())->willThrow(new \Exception('Error'));

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

    public function testGivenProcessFailing_WhenCommandExecuted_ThenErrorIsDisplayed()
    {
        // given
        /** @var GitCheckoutProcessFactory $factory */
        $factory = $this->prophesize(GitCheckoutProcessFactory::class);

        $process = $this->prophesize(Process::class);
        $process->run()->shouldBeCalled();
        $this->gitCheckoutValidator->checkStatus($process->reveal())->willThrow(new \Exception('error'));
        $factory->getFullCloneProcess('abc')->willReturn($process->reveal())->shouldBeCalled();

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

    public function testGivenProcessIsSuccessful_WhenCommandExecuted_ThenReturnCodeIsSuccess()
    {
        // given
        /** @var GitCheckoutProcessFactory $processFactory */
        $processFactory = $this->prophesize(GitCheckoutProcessFactory::class);

        $fullCloneProcess = $this->prophesize(Process::class);
        $fullCloneProcess->run();
        $fullCloneProcess->isSuccessful()->willReturn(true);

        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run();

        $processFactory->getFullCloneProcess('abc')->willReturn($fullCloneProcess->reveal());
        $processFactory->getCheckoutCommitProcess('sha123')->willReturn($checkoutCommitProcess->reveal());

        $this->container->set('trivago.rumi.process.git_checkout_process_factory', $processFactory->reveal());

        $result = $this->SUT->run(new ArrayInput(
            [
                'repository' => 'abc',
                'commit' => 'sha123',
            ]
        ), $this->output);

        $this->assertEquals($result, ReturnCodes::SUCCESS);
    }
}
