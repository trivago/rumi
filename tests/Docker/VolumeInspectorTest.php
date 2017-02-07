<?php

/*
 * Copyright 2016 trivago GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Trivago\Rumi\Docker;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\VolumeInspectProcessFactory;

/**
 * @covers \Trivago\Rumi\Docker\VolumeInspector
 */
class VolumeInspectorTest extends TestCase
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
