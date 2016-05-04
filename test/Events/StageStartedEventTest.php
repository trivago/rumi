<?php
/**
 * @author jsacha
 * @since 29/04/16 15:08
 */

namespace jakubsacha\Rumi\Events;


class StageStartedEventTest extends \PHPUnit_Framework_TestCase
{

    public function testGivenNameAndJobs_WhenNewInstanceCreated_ThenGettersAreFine()
    {
        // given
        $name = 'abc';
        $jobs = ['abc', 'def'];

        // when
        $event = new StageStartedEvent($name, $jobs);

        // then

        $this->assertEquals($name, $event->getName());
        $this->assertEquals($jobs, $event->getJobs());
    }
}
