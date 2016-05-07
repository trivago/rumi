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
