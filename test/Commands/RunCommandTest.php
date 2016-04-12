<?php
/**
 * @author jsacha
 * @since 20/02/16 22:01
 */

namespace jakubsacha\Rumi\Commands;


use jakubsacha\Rumi\Process\RunningProcessesFactory;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Process\Process;

/**
 * @covers jakubsacha\Rumi\Commands\RunCommand
 */
class RunCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var RunCommand
     */
    private $oCommand;

    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->output = new BufferedOutput();

        vfsStream::setup('directory');

        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('../../config/services.xml');

        $this->oCommand = new RunCommand($this->container);
        $this->oCommand->setWorkingDir(vfsStream::url('directory'));
    }

    public function testGivenNoCiYamlFile_WhenExecuted_ThenDisplaysErrorMessage()
    {
        // given

        // when
        $returnCode = $this->oCommand->run(new ArrayInput([]), $this->output);

        // then
        $this->assertSame("Required file '" . RunCommand::CONFIG_FILE . "' does not exist", trim($this->output->fetch()));
        $this->assertEquals(-1, $returnCode);
    }

    public function testGivenCiYamlSyntaxIsWrong_WhenExecuted_ThenDisplaysErrorMessage()
    {
        // given
        file_put_contents(vfsStream::url('directory') . '/' . RunCommand::CONFIG_FILE, 'wrong::' . PHP_EOL . '::yaml_file');

        // when
        $returnCode = $this->oCommand->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $this->assertSame('Unable to parse at line 2 (near "::yaml_file").', trim($this->output->fetch()));
        $this->assertEquals(-1, $returnCode);
    }

    public function testGivenValidCiYamlAndBuildIsOk_WhenExecuted_ThenDisplaysConfirmationMessage()
    {
        // given
        $oStartProcess = $this->getStartProcess(true);
        $oTearDownProcess = $this->getTearDownProcess();

        $oProcessFactory = $this->getProcessFactoryMock($oStartProcess, $oTearDownProcess);

        $this->container->set('jakubsacha.rumi.process.running_processes_factory', $oProcessFactory->reveal());

        file_put_contents(vfsStream::url('directory') . "/" . RunCommand::CONFIG_FILE, file_get_contents('fixtures/passing-.rumi.yml'));

        // when
        $returnCode = $this->oCommand->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $sCommandOutput = $this->output->fetch();

        $this->assertStringStartsWith('Stage: "Stage one"', trim($sCommandOutput));
        $this->assertContains('Build successful', $sCommandOutput);
        $this->assertEquals(0, $returnCode);
    }

    public function testGivenValidCiYamlAndBuildFails_WhenExecuted_ThenDisplaysConfirmationMessage()
    {
        // given
        $oStartProcess = $this->getStartProcess(false);
        $oStartProcess->getErrorOutput()->shouldBeCalled();
        $oTearDownProcess = $this->getTearDownProcess();

        /** @var RunningProcessesFactory $oProcessFactory */
        $oProcessFactory = $this->getProcessFactoryMock($oStartProcess, $oTearDownProcess);

        $this->container->set('jakubsacha.rumi.process.running_processes_factory', $oProcessFactory->reveal());


        file_put_contents(vfsStream::url('directory') . "/" . RunCommand::CONFIG_FILE, file_get_contents('fixtures/failing-.rumi.yml'));

        // when
        $returnCode = $this->oCommand->run(new ArrayInput(['volume' => '.']), $this->output);

        // then
        $sCommandOutput = $this->output->fetch();

        $this->assertStringStartsWith('Stage: "Stage one"', trim($sCommandOutput));
        $this->assertContains('failed', $sCommandOutput);
        $this->assertEquals(-1, $returnCode);
    }

    /**
     * @param $isSuccessful
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function getStartProcess($isSuccessful)
    {
        $oStartProcess = $this->prophesize(Process::class);
        $oStartProcess->start()->shouldBeCalled();
        $oStartProcess->isRunning()->shouldBeCalled();
        $oStartProcess->isSuccessful()->willReturn($isSuccessful)->shouldBeCalled();
        $oStartProcess->getOutput()->shouldBeCalled();
        $oStartProcess->getErrorOutput()->shouldBeCalled();

        return $oStartProcess;
    }

    /**
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function getTearDownProcess()
    {
        $oTearDownProcess = $this->prophesize(Process::class);
        $oTearDownProcess->run()->shouldBeCalled();

        return $oTearDownProcess;
    }

    /**
     * @param $oStartProcess
     * @param $oTearDownProcess
     * @return RunningProcessesFactory
     */
    protected function getProcessFactoryMock($oStartProcess, $oTearDownProcess)
    {
        /** @var RunningProcessesFactory $oProcessFactory */
        $oProcessFactory = $this->prophesize(RunningProcessesFactory::class);

        $oProcessFactory->getJobStartProcess(Argument::any(), Argument::any(), Argument::any())
            ->willReturn($oStartProcess->reveal());

        $oProcessFactory->getTearDownProcess(Argument::any(), Argument::any())
            ->willReturn($oTearDownProcess->reveal());

        return $oProcessFactory;
    }
}
