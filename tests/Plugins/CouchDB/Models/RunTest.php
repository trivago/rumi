<?php

/*
 * Copyright 2016 trivago GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Trivago\Rumi\Plugins\CouchDB\Models;


use PHPUnit\Framework\TestCase;

/**
 * @covers \Trivago\Rumi\Plugins\CouchDB\Models\Run
 */
class RunTest extends TestCase
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
