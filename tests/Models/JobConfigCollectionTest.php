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

namespace Trivago\Rumi\Models;

/**
 * @covers \Trivago\Rumi\Models\JobConfigCollection
 */
class JobConfigCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobConfigCollection
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new JobConfigCollection();
    }

    public function testGivenJob_WhenAddedToCollection_ThenItsPartOfCollection()
    {
        // given
        $job = $this->prophesize(JobConfig::class)->reveal();

        // when
        $this->SUT->add($job);

        // then
        $this->assertEquals($job, $this->SUT->getJobs()[0]);
    }
}
