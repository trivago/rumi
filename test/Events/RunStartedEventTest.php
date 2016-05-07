<?php
/**
 * @author jsacha
 *
 * @since 29/04/16 15:03
 */

namespace Trivago\Rumi\Events;

use Trivago\Rumi\Models\RunConfig;

/**
 * @covers \Trivago\Rumi\Events\RunStartedEvent
 */
class RunStartedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenConfig_WhenNewInstanceCreated_ThenGetterWorks()
    {
        //given
        $runConfig = new RunConfig(['abc']);

        // when
        $event = new RunStartedEvent($runConfig);

        // then
        $this->assertEquals($runConfig, $event->getRunConfig());
    }
}
