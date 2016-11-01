<?php
/**
 * Created by PhpStorm.
 * User: ppokalyukhina
 * Date: 01/11/16
 * Time: 15:30
 */

namespace Trivago\Rumi\Commands;
use org\bovigo\vfs\vfsStream;
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
        $this->input = $this->prophesize(InputInterface::class);

        $this->gitCheckoutExecuteCommands = new GitCheckoutExecuteCommands(
            $this->gitCheckoutValidator->reveal(),
            $this->processFactory->reveal(),
            $this->configReader->reveal()
        );

        $this->gitCheckoutExecuteCommands->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenWorkingDirIsEmpty_WhenCommandExecuted_ThenFullCheckoutIsDone()
    {
        $symfonyProcess = $this->prophesize(Process::class);
        $symfonyProcess->isSuccessful()->willReturn(true)->shouldBeCalled();

        $this->gitCheckoutExecuteCommands->executeGitCloneBranch($this->input, $this->output);

        $this->assertTrue(true);
    }
}
