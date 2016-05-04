<?php
/**
 * @author jsacha
 *
 * @since 02/03/16 12:07
 */

namespace jakubsacha\Rumi\Models;

use jakubsacha\Rumi\Process\RunningProcessesFactory;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

/**
 * @covers jakubsacha\Rumi\Models\RunningCommand
 */
class RunningCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RunningCommand
     */
    private $SUT;

    /**
     * @var JobConfig
     */
    private $job_config;

    /**
     * @var RunningProcessesFactory
     */
    private $running_process_factory;

    public function setUp()
    {
        $this->job_config = $this->prophesize(JobConfig::class);
        $this->running_process_factory = $this->prophesize(RunningProcessesFactory::class);
        $this->SUT = new RunningCommand(
            $this->job_config->reveal(),
            'path',
            $this->running_process_factory->reveal()
        );
    }

    public function testGivenJobConfig_WhenGetCommandCalled_ThenItReturnsValidCommand()
    {
        $this->job_config->getCommandsAsString()->willReturn('test_command');

        $this->assertEquals('test_command', $this->SUT->getCommand());
    }

    public function testGivenJobConfig_WhenGetNameCalled_ThenItReturnsValidName()
    {
        $this->job_config->getName()->willReturn('test_command');

        $this->assertEquals('test_command', $this->SUT->getJobName());
    }

    public function testGivenProcessIsStarted_WhenGetProcessCalled_ThenItReturnsValidProcess()
    {
        //given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->start()->shouldBeCalled();
        $process = $process_prophecy->reveal();

        $this->job_config->getCommandsAsString()->willReturn('echo abc');
        $this->job_config->getCiContainer()->willReturn('ci_image');

        $this->running_process_factory->getJobStartProcess('path', Argument::type('string'), 'ci_image')->willReturn($process);

        // when
        $this->SUT->start();

        //then
        $this->assertSame($process, $this->SUT->getProcess());
    }

    public function testGivenProcessIsRunning_WhenTearDownCalled_ThenItRunsTearDown()
    {
        //given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->run()->shouldBeCalled();
        $process = $process_prophecy->reveal();

        $this->running_process_factory->getTearDownProcess('path', Argument::type('string'))->willReturn($process);

        // when
        $this->SUT->tearDown();

        //then
    }

    public function testGivenProcessIsRunning_WhenIsRunningCalled_ThenItReturnsValidStatus()
    {
        // given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->start()->shouldBeCalled();
        $process_prophecy->isRunning()->willReturn(true);
        $process = $process_prophecy->reveal();

        $this->job_config->getCommandsAsString()->willReturn('echo abc');
        $this->job_config->getCiContainer()->willReturn('ci_image');

        $this->running_process_factory->getJobStartProcess('path', Argument::type('string'), 'ci_image')->willReturn($process);

        // when
        $this->SUT->start();
        $isRunning = $this->SUT->isRunning();

        // then
        $this->assertTrue($isRunning);
    }

    public function testGivenProcessDone_WhenGetOutputCalled_ThenItReturnsIt()
    {
        // given
        $process_prophecy = $this->prophesize(Process::class);
        $process_prophecy->start()->shouldBeCalled();
        $process_prophecy->getOutput()->willReturn('output');
        $process_prophecy->getErrorOutput()->willReturn('erroroutput');

        $process = $process_prophecy->reveal();

        $this->job_config->getCommandsAsString()->willReturn('echo abc');
        $this->job_config->getCiContainer()->willReturn('ci_image');

        $this->running_process_factory->getJobStartProcess('path', Argument::type('string'), 'ci_image')->willReturn($process);

        // when
        $this->SUT->start();
        $output = $this->SUT->getOutput();

        // then
        $this->assertEquals('outputerroroutput', $output);
    }
}
