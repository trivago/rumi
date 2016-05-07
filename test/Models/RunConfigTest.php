<?php
/**
 * @author jsacha
 *
 * @since 29/04/16 13:54
 */

namespace Trivago\Rumi\Models;

/**
 * @covers \Trivago\Rumi\Models\RunConfig
 */
class RunConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNewInstanceIsCreated_WhenGetterAccessed_ThenItReturnsValidData()
    {
        //given
        $stages = ['abc'];

        // when
        $SUT = new RunConfig($stages);

        // then
        $this->assertEquals($stages, $SUT->getStages());
    }
}
