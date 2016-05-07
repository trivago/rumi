<?php
/**
 * @author jsacha
 *
 * @since 06/05/16 09:19
 */

namespace Trivago\Rumi\Plugins\CouchDB\Models;

/**
 * @covers Trivago\Rumi\Plugins\CouchDB\Models\Run
 */
class RunTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenCommitId_WhenNewInstanceCreated_ThenGettersAreFine()
    {
        //given
        $commit = 'commit_id';

        // when
        $run = new Run($commit);

        // then
        $this->assertEquals($commit, $run->getCommit());
    }

    public function testGivenStage_WhenItsAdded_ThenGetterWorks()
    {
        // given
        $stage = new Stage('abc');

        // when
        $run = new Run('commit');
        $run->addStage($stage);

        // then
        $this->assertContains($stage, $run->getStages());
    }
}
