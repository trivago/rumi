<?php
/**
 * @author jsacha
 * @since 20/11/2016 12:58
 */

namespace Trivago\Rumi\Models;


class CacheConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testGivenValidConfig_WhenEntityCreated_ThenGetDirectoriesReturnsContent()
    {
        //given
        $directories =  ['a', 'b', 'c'];

        // when
        $cacheConfig = new CacheConfig($directories);

        // then
        $this->assertEquals($directories, iterator_to_array($cacheConfig, true));
        $this->assertEquals(3, $cacheConfig->count());
    }

}
