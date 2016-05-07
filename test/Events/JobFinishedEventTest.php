<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 20:39
 */

namespace Trivago\Rumi\Events;

class JobFinishedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNewInstanceIsCreated_WhenStatusPassed_YouCanUseGetterToAccessIt()
    {
        //given
        $status = JobFinishedEvent::STATUS_SUCCESS;
        $name = 'name';
        $output = 'abc';

        // when
        $SUT = new JobFinishedEvent($status, $name, $output);

        // then
        $this->assertEquals($status, $SUT->getStatus());
        $this->assertEquals($name, $SUT->getName());
        $this->assertEquals($output, $SUT->getOutput());
    }
}
