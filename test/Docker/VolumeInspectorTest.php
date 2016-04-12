<?php

namespace jakubsacha\Rumi\Docker;

use jakubsacha\Rumi\Process\VolumeInspectProcessFactory;
use Symfony\Component\Process\Process;

/**
 * @covers jakubsacha\Rumi\Docker\VolumeInspector
 */
class VolumeInspectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VolumeInspector
     */
    private $oSUT;

    /**
     * @var VolumeInspectProcessFactory
     */
    private $oProcessFactory;

    protected function setUp()
    {
        $this->oProcessFactory = $this->prophesize(VolumeInspectProcessFactory::class);

        $this->oSUT = new VolumeInspector(
            $this->oProcessFactory->reveal()
        );
    }

    public function testGivenVolumeName_WhenVolumeGetRealPathCalled_ThenPathIsReturned()
    {
        // given
        $oProcess = $this->prophesize(Process::class);
        $oProcess->run()->shouldBeCalled();
        $oProcess->isSuccessful()->willReturn(true);
        $oProcess->getOutput()->willReturn('[
    {
        "Name": "8ab9841d40db34620455467f5babb50e10a35da8e47bb74ca10c4675ac2f7d4e",
        "Driver": "local",
        "Mountpoint": "__volume_real_path__"
    }
]
');
        $this->oProcessFactory->getInspectProcess('__volume__')->willReturn($oProcess);

        // when
        $sPath = $this->oSUT->getVolumeRealPath('__volume__');

        // then
        $this->assertEquals('__volume_real_path__/', $sPath);
    }


    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can not read volume informations: Error: No such volume: __volume__
     */
    public function testGivenVolumeName_WhenVolumeGetRealPathCalled_ThenCommandFails()
    {
        // given
        $oProcess = $this->prophesize(Process::class);
        $oProcess->run()->shouldBeCalled();
        $oProcess->isSuccessful()->willReturn(false);
        $oProcess->getErrorOutput()->willReturn('Error: No such volume: __volume__');

        $this->oProcessFactory->getInspectProcess('__volume__')->willReturn($oProcess);

        // when
        $sPath = $this->oSUT->getVolumeRealPath('__volume__');

        // then
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can use only local volumes
     */
    public function testGivenVolumeName_WhenDriverIsNotLocal_ThenCommandFails()
    {
        // given
        $oProcess = $this->prophesize(Process::class);
        $oProcess->run()->shouldBeCalled();
        $oProcess->isSuccessful()->willReturn(true);
        $oProcess->getOutput()->willReturn('[
    {
        "Name": "8ab9841d40db34620455467f5babb50e10a35da8e47bb74ca10c4675ac2f7d4e",
        "Driver": "gluster",
        "Mountpoint": "__volume_real_path__"
    }
]
');

        $this->oProcessFactory->getInspectProcess('__volume__')->willReturn($oProcess);

        // when
        $sPath = $this->oSUT->getVolumeRealPath('__volume__');

        // then
    }



    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Docker response is not valid
     */
    public function testGivenVolumeName_WhenInspectReturnsShit_ThenCommandFails()
    {
        // given
        $oProcess = $this->prophesize(Process::class);
        $oProcess->run()->shouldBeCalled();
        $oProcess->isSuccessful()->willReturn(true);
        $oProcess->getOutput()->willReturn('some crap');

        $this->oProcessFactory->getInspectProcess('__volume__')->willReturn($oProcess);

        // when
        $sPath = $this->oSUT->getVolumeRealPath('__volume__');

        // then
    }
}
