<?php
namespace jakubsacha\Rumi\Process;

/**
 * @covers jakubsacha\Rumi\Process\RunningProcessesFactory
 */
class RunningProcessesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RunningProcessesFactory
     */
    private $oSUT;

    public function setUp()
    {
        $this->oSUT = new RunningProcessesFactory();
    }

    public function testGetJobStartProcess()
    {
        $oProcess = $this->oSUT->getJobStartProcess(
            'a', 'b', 'c'
        );
        $this->assertEquals('docker-compose -f a run --name b c', $oProcess->getCommandLine());
    }

    public function testGetTearDownProcess()
    {
        $oProcess = $this->oSUT->getTearDownProcess(
            'a', 'b'
        );
        $this->assertEquals('docker rm -f b;
            docker-compose -f a rm --force;
            docker rm -f $(docker-compose -f a ps -q)', $oProcess->getCommandLine());
    }

}
