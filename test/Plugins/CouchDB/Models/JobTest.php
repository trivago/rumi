<?php
/**
 * @author jsacha
 *
 * @since 06/05/16 09:15
 */

namespace Trivago\Rumi\Plugins\CouchDB\Models;

/**
 * @covers Trivago\Rumi\Plugins\CouchDB\Models\Job
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNameAndStatus_WhenNewInstanceCreated_ThenGettersAreFine()
    {
        // given
        $name = 'name';
        $status = 'status';

        // when
        $job = new Job($name, $status);

        // then
        $this->assertEquals($name, $job->getName());
        $this->assertEquals($status, $job->getStatus());
    }

    public function testGivenOutput_WhenItIsSet_ThenGetterWorks()
    {
        //given
        $output = 'abc';

        // when
        $job = new Job('a', 'b');
        $job->setOutput($output);

        // then
        $this->assertEquals($output, $job->getOutput());
    }

    public function testGivenNewStatus_WhenItsSet_ThenGetterWorks()
    {
        //given
        $status = 'new_status';

        // when
        $job = new Job('a', 'b');
        $job->setStatus($status);

        // then
        $this->assertEquals($status, $job->getStatus());
    }
}
