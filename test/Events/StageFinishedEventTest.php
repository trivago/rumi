<?php
/**
 * @author jsacha
 * @since 29/04/16 15:06
 */

namespace jakubsacha\Rumi\Events;

/**
 * @covers \jakubsacha\Rumi\Events\StageFinishedEvent
 * @covers \jakubsacha\Rumi\Events\AbstractFinishedEvent
 */
class StageFinishedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNameAndStatus_WhenNewInstanceCreated_GettersAreFine()
    {
        //given
        $name = 'abc';
        $status = StageFinishedEvent::STATUS_FAILED;

        // when
        $event = new StageFinishedEvent($status, $name);

        // then

        $this->assertEquals($name, $event->getName());
        $this->assertEquals($status, $event->getStatus());
    }
}
