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


use PHPUnit\Framework\TestCase;

/**
 * @covers \Trivago\Rumi\Models\RunConfig
 */
class RunConfigTest extends TestCase
{
    public function testGivenNewInstanceIsCreated_WhenGetterAccessed_ThenItReturnsValidData()
    {
        //given
        $stages = ['abc'=>[]];
        $caches = ['cache', 'cache2'];
        $mergeBranch = 'merge_branch';

        // when
        $SUT = new RunConfig(
            $this->prophesize(StagesCollection::class)->reveal(),
            new CacheConfig($caches),
            $mergeBranch
        );

        // then
        $this->assertInstanceOf(StagesCollection::class, $SUT->getStagesCollection());
        $this->assertEquals($caches, iterator_to_array($SUT->getCache(), true));
        $this->assertEquals($mergeBranch, $SUT->getMergeBranch());
    }
}
