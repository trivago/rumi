<?php

namespace Trivago\Rumi\Process;

/**
 * @covers Trivago\Rumi\Process\RunningProcessesFactory
 */
class RunningProcessesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RunningProcessesFactory
     */
    private $SUT;

    public function setUp()
    {
        $this->SUT = new RunningProcessesFactory();
    }

    public function testGetJobStartProcess()
    {
        $process = $this->SUT->getJobStartProcess(
            'a', 'b', 'c'
        );
        $this->assertEquals('docker-compose -f a run --name b c', $process->getCommandLine());
    }

    public function testGetTearDownProcess()
    {
        $process = $this->SUT->getTearDownProcess(
            'a', 'b'
        );
        $this->assertEquals('docker rm -f b;
            docker-compose -f a rm --force;
            docker rm -f $(docker-compose -f a ps -q)', $process->getCommandLine());
    }
}
