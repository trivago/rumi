<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 01/11/16
 * Time: 15:30
 */

namespace Trivago\Rumi\Commands;
use Error;
use org\bovigo\vfs\vfsStream;
use Prophecy\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\GitCheckoutProcessFactory;
use Trivago\Rumi\Services\ConfigReader;
use Trivago\Rumi\Validators\GitCheckoutValidator;


/**
 * @covers GitCheckoutExecuteCommands
 */
class GitCheckoutExecuteCommandsTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @var GitCheckoutValidator
    */
    private $gitCheckoutValidator;

    /**
     * @var GitCheckoutProcessFactory
     */
    private $processFactory;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var GitCheckoutExecuteCommands
     */
    private $gitCheckoutExecuteCommands;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var BufferedOutput
     */
    private $output;

    public function setUp()
    {
        vfsStream::setup('directory');

        $this->output = new BufferedOutput();

        $this->gitCheckoutValidator = $this->prophesize(GitCheckoutValidator::class);
        $this->processFactory = $this->prophesize(GitCheckoutProcessFactory::class);
        $this->configReader = $this->prophesize(ConfigReader::class);
        $this->input = $this->prophesize(InputInterface::class)->reveal();

        $this->gitCheckoutExecuteCommands = new GitCheckoutExecuteCommands(
            $this->gitCheckoutValidator->reveal(),
            $this->processFactory->reveal(),
            $this->configReader->reveal()
        );

        $this->gitCheckoutExecuteCommands->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenWorkingDirContainsDotGit_WhenCommandExecuted_ThenFetchIsDone()
    {
        touch(vfsStream::url('directory').'/.git');

        $fetchProcess = $this->prophesize(Process::class);
        $fetchProcess->run();
        $fetchProcess->isSuccessful()->willReturn(true);

        $this->processFactory->getFetchProcess()->willReturn($fetchProcess->reveal());

        $this->assertTrue(true);
    }

    public function testGivenWorkingDirIsEmpty_WhenCommandExecuted_ThenFullCheckoutIsDone()
    {
        $fullCloneProcess = $this->prophesize(Process::class);
        $fullCloneProcess->run();
        $fullCloneProcess->isSuccessful()->willReturn(true);

        $this->processFactory->getFullCloneProcess('abc')->willReturn($fullCloneProcess->reveal());

        $this->assertTrue(true);
    }

    public function testGivenMergeBranchIsNotSpecified_WhenCommandExecuted_ThenItMergesWithMaster() {
        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run();
        $checkoutCommitProcess->isSuccessful()->willReturn(true);

        $this->processFactory->getCheckoutCommitProcess('commmit')->willReturn($checkoutCommitProcess->reveal());

        $this->assertTrue(true);
    }

    public function testGivenMergeBranchIsSpecified_WhenCommandExecuted_ThenItMergesWithIt() {
        touch(vfsStream::url('directory').'/.git');

        file_put_contents(vfsStream::url('directory').'/'.CommandAbstract::DEFAULT_CONFIG, 'merge_branch: abc');
        $checkoutCommitProcess = $this->prophesize(Process::class);
        $checkoutCommitProcess->run();
        $checkoutCommitProcess->isSuccessful()->willReturn(true);

        $this->processFactory->getCheckoutCommitProcess('commmit')->willReturn($checkoutCommitProcess->reveal());

        $this->assertTrue(true);
    }


//    /**
//     * @expectedException \Exception
//     */
    public function testGivenMergeFails_WhenCommandExecuted_ThenItReturnsValidOutput() {
        touch(vfsStream::url('directory').'/.git');
        file_put_contents(vfsStream::url('directory').'/'.CommandAbstract::DEFAULT_CONFIG, 'merge_branch: origin/master');

        $this->configReader->getConfig(vfsStream::url('directory'), "config_file");

        $mergeProcess = $this->prophesize(Process::class);
        $mergeProcess->isSuccessful()->willReturn(false);
        $mergeProcess->run();

//        $this->gitCheckoutValidator->checkStatus($mergeProcess->reveal())->willThrow(new \Exception());

//        $this->processFactory->getMergeProcess('origin/master')->willReturn($mergeProcess->reveal());

        $this->gitCheckoutExecuteCommands->executeGitMergeBranchProcess(null, $this->output);

    }
}
