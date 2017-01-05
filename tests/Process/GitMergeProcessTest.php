<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 05/01/17
 * Time: 20:27
 */

namespace Trivago\Rumi\Services;


use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Process\GitMergeProcess;
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
     * @var GitMergeProcess
     */
    private $gitMergeProcess;


    public function setUp() {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        $this->configReader = $this->prophesize(ConfigReader::class);

        $this->gitMergeProcess = new GitMergeProcess(
            $this->configReader->reveal(),
            $this->processFactory->reveal(),
            $this->gitCheckoutValidator->reveal()
        );
    }

    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt()
    {
        touch(vfsStream::url('directory').'/git');
        $runConfig = $this->prophesize(RunConfig::class);
        $runConfig->getMergeBranch()->willReturn('origin/master');

        $this->configReader->getRunConfig("/git", 'config_file')->willReturn(
            $runConfig->reveal()
        );

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run();

        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal());

        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());
        $this->gitMergeProcess->executeGitMergeBranchProcess("/git", 'config_file', $this->output);

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

        $this->configReader->getRunConfig('/git', 'config_file')->willReturn(
            $runConfig->reveal()
        );

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->run()->shouldBeCalled();

        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal())->willThrow(new \Exception('Error'));
        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $this->gitMergeProcess->executeGitMergeBranchProcess("/git", 'config_file', $this->output);
    }
}