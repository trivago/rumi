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

namespace Trivago\Rumi\Commands\CacheStore;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Process\Process;
use Trivago\Rumi\Process\CacheProcessFactory;

/**
 * @covers \Trivago\Rumi\Commands\CacheStore\CacheStoreDir
 */
class CacheStoreDirTest extends TestCase
{
    /**
     * @var CacheProcessFactory|ObjectProphecy
     */
    private $cacheProcessFactory;

    /**
     * @var CacheStoreDir
     */
    private $SUT;

    public function setUp()
    {
        $this->cacheProcessFactory = $this->prophesize(CacheProcessFactory::class);
        $this->SUT = new CacheStoreDir(
            $this->cacheProcessFactory->reveal()
        );
        vfsStream::setup('directory');
    }

    public function testGivenSourceAndDest_WhenStoreExecuted_ThenItStores()
    {
        //given
        $process = $this->getProcessMock(true);

        $source = $this->getExistingSourcePath();
        $cacheDestinationDirectory = 'dst';

        $this
            ->cacheProcessFactory
            ->getCacheStoreProcess($source, $cacheDestinationDirectory)
            ->willReturn($process);
        //when

        $returnValue = $this->SUT->store($source, $cacheDestinationDirectory);

        //then
        $this->assertStringStartsWith('Storing cache for: ' . $source . '... ', $returnValue);
    }

    /**
     * @expectedException \Exception
     */
    public function testGivenSourceAndDest_WhenProcessFails_ThenItThrowsException()
    {
        //given
        $process = $this->getProcessMock(false);

        $source = $this->getExistingSourcePath();
        $cacheDestinationDirectory = 'dst';

        $this
            ->cacheProcessFactory
            ->getCacheStoreProcess($source, $cacheDestinationDirectory)
            ->willReturn($process);
        //when

        $returnValue = $this->SUT->store($source, $cacheDestinationDirectory);

        //then
        $this->assertStringStartsWith('Storing cache for: ' . $source . '... ', $returnValue);
    }

    public function testGivenSourceDirectoryDoesNotExist_WhenExecuted_ThenItReturnsWarningMessage()
    {
        // given
        $nonExisting = 'non_existing_directory';

        // when
        $returnValue = $this->SUT->store($nonExisting, 'some_target');

        // then
        $this->assertEquals('Source directory: ' . $nonExisting . ' does not exist', $returnValue);
    }

    /**
     * @param $isSuccesful
     *
     * @return ObjectProphecy|Process
     */
    private function getProcessMock($isSuccesful)
    {
        $process = $this->prophesize(Process::class);
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn($isSuccesful);
        $process->getOutput()->willReturn('output');
        $process->getErrorOutput()->willReturn('error_output');

        return $process;
    }

    /**
     * @return string
     */
    private function getExistingSourcePath()
    {
        mkdir(vfsStream::url('directory') . '/source_file');

        return vfsStream::url('directory') . '/source_file';
    }
}
