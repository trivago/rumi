<?php

namespace Trivago\Rumi\Docker;

use Trivago\Rumi\Process\VolumeInspectProcessFactory;
use Symfony\Component\Process\Process;

/**
 * @covers Trivago\Rumi\Docker\VolumeInspector
 */
class VolumeInspectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VolumeInspector
     */
    private $SUT;

    /**
     * @var VolumeInspectProcessFactory
     */
    private $processFactory;

    protected function setUp()
    {
        $this->processFactory = $this->prophesize(VolumeInspectProcessFactory::class);

        $this->SUT = new VolumeInspector(
            $this->processFactory->reveal()
        );
    }

    public function testGivenVolumeName_WhenVolumeGetRealPathCalled_ThenPathIsReturned()
    {
        // given
        $process = $this->prophesize(Process::class);
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->willReturn('[
    {
        "Name": "8ab9841d40db34620455467f5babb50e10a35da8e47bb74ca10c4675ac2f7d4e",
        "Driver": "local",
        "Mountpoint": "__volume_real_path__"
    }
]
');
        $this->processFactory->getInspectProcess('__volume__')->willReturn($process);

        // when
        $path = $this->SUT->getVolumeRealPath('__volume__');

        // then
        $this->assertEquals('__volume_real_path__/', $path);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can not read volume informations: Error: No such volume: __volume__
     */
    public function testGivenVolumeName_WhenVolumeGetRealPathCalled_ThenCommandFails()
    {
        // given
        $process = $this->prophesize(Process::class);
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);
        $process->getErrorOutput()->willReturn('Error: No such volume: __volume__');

        $this->processFactory->getInspectProcess('__volume__')->willReturn($process);

        // when
        $path = $this->SUT->getVolumeRealPath('__volume__');

        // then
        // expected exception
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can use only local volumes
     */
    public function testGivenVolumeName_WhenDriverIsNotLocal_ThenCommandFails()
    {
        // given
        $process = $this->prophesize(Process::class);
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->willReturn('[
    {
        "Name": "8ab9841d40db34620455467f5babb50e10a35da8e47bb74ca10c4675ac2f7d4e",
        "Driver": "gluster",
        "Mountpoint": "__volume_real_path__"
    }
]
');

        $this->processFactory->getInspectProcess('__volume__')->willReturn($process);

        // when
        $path = $this->SUT->getVolumeRealPath('__volume__');

        // then
        // expected exception
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Docker response is not valid
     */
    public function testGivenVolumeName_WhenInspectReturnsShit_ThenCommandFails()
    {
        // given
        $process = $this->prophesize(Process::class);
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->willReturn('some crap');

        $this->processFactory->getInspectProcess('__volume__')->willReturn($process);

        // when
        $path = $this->SUT->getVolumeRealPath('__volume__');

        // then
        // expected exception
    }
}
