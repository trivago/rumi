<?php
/**
 * @author jsacha
 *
 * @since 06/05/16 09:25
 */

namespace jakubsacha\Rumi\Plugins\CouchDB\Models;

/**
 * @covers jakubsacha\Rumi\Plugins\CouchDB\Models\Stage
 */
class StageTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenName_WhenNewStageCreated_ThenGetterWorks()
    {
        // given
        $name = 'name';

        // when
        $stage = new Stage($name);

        // then
        $this->assertEquals($name, $stage->getName());
    }

    public function testGivenJob_WhenItsAdded_ThenGetAllWorks()
    {
        // given
        $job = new Job('Test name', 'status');

        // when
        $stage = new Stage('abc');
        $stage->addJob($job);

        // then
        $this->assertContains($job, $stage->getJobs());
    }

    public function testGivenJob_WhenItsAdded_ThenGetByNameWorks()
    {
        // given
        $name = 'Test name';

        $job = new Job($name, 'status');

        // when
        $stage = new Stage('abc');
        $stage->addJob($job);

        // then
        $this->assertEquals($job, $stage->getJob($name));
    }

    public function testGivenJobIsNotThere_WhenItsNotAdded_ThenGetByNameReturnsNull()
    {
        // given

        // when
        $stage = new Stage('abc');

        // then
        $this->assertNull($stage->getJob('abc'));
    }
}
