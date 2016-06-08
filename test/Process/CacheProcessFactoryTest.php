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

use Trivago\Rumi\Process\CacheProcessFactory;

/**
 * @covers Trivago\Rumi\Process\CacheProcessFactory
 */
class CacheProcessFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CacheProcessFactory
     */
    private $SUT;

    public function setUp()
    {
        $this->SUT = new CacheProcessFactory();
    }

    public function testGetCacheStoreProcess()
    {
        //given

        //when
        $process = $this->SUT->getCacheStoreProcess('a', 'b');

        // then
        $this->assertEquals('(
                    flock -x 200 || exit 1;
                    rsync --delete -axH a/ b/data/a
                ) 200>b/.rsync.lock', trim($process->getCommandLine()));

        $this->assertEquals(600, $process->getTimeout());
        $this->assertEquals(600, $process->getIdleTimeout());
    }

    public function testGetaCacheStoreProcess()
    {
        //given

        //when
        $process = $this->SUT->getCreateCacheDirectoryProcess('a');

        // then
        $this->assertEquals('mkdir -p a/data/', trim($process->getCommandLine()));
    }

    public function testGetCacheRestoreProcess()
    {
        //given

        //when
        $process = $this->SUT->getCacheRestoreProcess('a', 'b');

        // then
        $this->assertEquals('(
                    flock -x 200 || exit 1;
                    rsync --delete -axH a . ;
                ) 200>b/.rsync.lock', trim($process->getCommandLine()));

        $this->assertEquals(600, $process->getTimeout());
        $this->assertEquals(600, $process->getIdleTimeout());
    }
}
