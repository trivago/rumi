<?php

namespace Trivago\Rumi\Process;

/**
 * @covers Trivago\Rumi\Process\VolumeInspectProcessFactory
 */
class VolumeInspectProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VolumeInspectProcessFactory
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new VolumeInspectProcessFactory();
    }

    public function testGetInspectProcess()
    {
        //given

        // when
        $process = $this->SUT->getInspectProcess('volume_name');

        // then
        $this->assertEquals('docker volume inspect \'volume_name\'', $process->getCommandLine());
    }
}
