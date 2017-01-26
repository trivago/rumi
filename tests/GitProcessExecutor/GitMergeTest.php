<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 05/01/17
 * Time: 20:27.
 */

namespace Trivago\Rumi\GitProcessExecutor;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;

class GitMergeProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $processFactory;

    /**
     * @var GitCheckoutValidator
     */
    private $gitCheckoutValidator;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var GitMerge
     */
    private $gitMerge;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        $this->configReader = $this->prophesize(ConfigReader::class);

        $this->gitMerge = new GitMerge(
            $this->configReader->reveal(),
            $this->processFactory->reveal(),
            $this->gitCheckoutValidator->reveal()
        );
    }

    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt()
    {
        touch(vfsStream::url('directory'));

        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn('origin/master');

        $this->configReader->getRunConfig(vfsStream::url('directory'), 'config_file')->willReturn(
            $runConfig->reveal()
        );

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();

        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal());

        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());
        $this->gitMerge->executeGitMergeBranchProcess('config_file', $this->output, vfsStream::url('directory'));

        $this->assertContains('Merging with origin/master', $this->output->fetch());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Can not clearly merge with origin/master
     */
    public function testGivenMergeFails_WhenCommandExecuted_ThenItReturnsValidOutput()
    {
        touch(vfsStream::url('directory').'/git');

        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn('origin/master');

        $this->configReader->getRunConfig(vfsStream::url('directory'), 'config_file')->willReturn(
            $runConfig->reveal()
        );

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();

        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal())->willThrow(new \Exception('Error'));
        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $this->gitMerge->executeGitMergeBranchProcess('config_file', $this->output, vfsStream::url('directory'));
    }

    public function testGivenConfigReaderThrowsException_whenIExecuteGitMergeBranchProcess_thenNothingIsReturned ()
    {
        touch(vfsStream::url('directory').'/git');

        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn(null);

        $this->configReader->getRunConfig(vfsStream::url('directory'), 'config_file')->willThrow(new \Exception('Error'));

        $mergeProcess = $this->prophesize(Process::class);
        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $this->gitMerge->executeGitMergeBranchProcess('config_file', $this->output, vfsStream::url('directory'));

        $this->assertEquals('', $this->output->fetch());
    }
}
