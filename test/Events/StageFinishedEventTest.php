<?php
/**
 * @author jsacha
 *
 * @since 29/04/16 15:06
 */

namespace Trivago\Rumi\Events;

/**
 * @covers \Trivago\Rumi\Events\StageFinishedEvent
 * @covers \Trivago\Rumi\Events\AbstractFinishedEvent
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
