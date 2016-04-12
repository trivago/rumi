<?php

namespace jakubsacha\Rumi\Process;

/**
 * @covers jakubsacha\Rumi\Process\VolumeInspectProcessFactory
 */
class VolumeInspectProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VolumeInspectProcessFactory
     */
    private $oSUT;

    protected function setUp()
    {
        $this->oSUT = new VolumeInspectProcessFactory();

    }

    public function testGetInspectProcess()
    {
        //given

        // when
        $oProcess = $this->oSUT->getInspectProcess('volume_name');

        // then
        $this->assertEquals('docker volume inspect \'volume_name\'', $oProcess->getCommandLine());
    }
}
