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

use PHPUnit\Framework\TestCase;
use Trivago\Rumi\Process\CacheProcessFactory;

/**
 * @covers \Trivago\Rumi\Process\CacheProcessFactory
 */
class CacheProcessFactoryTest extends TestCase
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
                    mkdir -p b/data/a;
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

    public function testGivenNestedDirectory_WhenCacheStoreExecuted_ThenNestedDirectoriesAreCreated()
    {
        if (exec('uname') === 'Darwin') {
            $this->markTestSkipped('flock not supported in unix');
        }

        // given
        $name = md5(time());
        $tests_dir = sys_get_temp_dir() . '/' . $name;
        mkdir($tests_dir);

        mkdir($tests_dir . '/source');
        mkdir($tests_dir . '/source/a');
        mkdir($tests_dir . '/source/a/b');
        touch($tests_dir . '/source/a/b/file');

        mkdir($tests_dir . '/target');

        $process = $this->SUT->getCacheStoreProcess('a/b', $tests_dir . '/target');
        $process->setWorkingDirectory($tests_dir . '/source');

        // when
        $returnCode = $process->run();

        // then
        $this->assertEquals(0, $returnCode, $process->getCommandLine() . PHP_EOL . $process->getErrorOutput());
        $this->assertTrue(file_exists($tests_dir . '/target/data/a/b/file'));
    }
}
