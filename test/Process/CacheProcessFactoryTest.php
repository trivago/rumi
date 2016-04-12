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
    private $oSUT;

    public function setUp()
    {
        $this->oSUT = new CacheProcessFactory();
    }

    public function testGetCacheStoreProcess()
    {
        //given

        //when
        $oProcess = $this->oSUT->getCacheStoreProcess('a', 'b');

        // then
        $this->assertEquals('(
                    flock -x 200 || exit 1;
                    rsync --delete -axH a/ b/data/a
                ) 200>b/.rsync.lock', trim($oProcess->getCommandLine()));

        $this->assertEquals(600, $oProcess->getTimeout());
        $this->assertEquals(600, $oProcess->getIdleTimeout());
    }

    public function testGetaCacheStoreProcess()
    {
        //given

        //when
        $oProcess = $this->oSUT->getCreateCacheDirectoryProcess('a');

        // then
        $this->assertEquals('mkdir -p a/data/', trim($oProcess->getCommandLine()));

    }

    public function testGetCacheRestoreProcess()
    {
        //given

        //when
        $oProcess = $this->oSUT->getCacheRestoreProcess('a', 'b');

        // then
        $this->assertEquals('(
                    flock -x 200 || exit 1;
                    rsync --delete -axH a . ;
                ) 200>b/.rsync.lock', trim($oProcess->getCommandLine()));

        $this->assertEquals(600, $oProcess->getTimeout());
        $this->assertEquals(600, $oProcess->getIdleTimeout());
    }
}
