<?php

use jakubsacha\Rumi\Process\CacheProcessFactory;

/**
 * @covers jakubsacha\Rumi\Process\CacheProcessFactory
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
