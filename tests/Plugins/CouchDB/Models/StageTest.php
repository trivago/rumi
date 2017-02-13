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
 * @covers \Trivago\Rumi\Plugins\CouchDB\Models\Stage
 */
class StageTest extends TestCase
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
