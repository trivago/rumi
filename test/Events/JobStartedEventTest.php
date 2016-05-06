<?php
/**
 * @author jsacha
 *
 * @since 06/05/16 09:29
 */

namespace jakubsacha\Rumi\Events;

/**
 * @covers jakubsacha\Rumi\Events\JobStartedEvent
 */
class JobStartedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenName_WhenNewInstanceCreated_ThenGetterWorks()
    {
        //given
        $name = 'name';

        // when
        $event = new JobStartedEvent($name);

        // then
        $this->assertEquals($name, $event->getName());
    }
}
