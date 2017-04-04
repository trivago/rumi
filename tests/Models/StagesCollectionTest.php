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
use Trivago\Rumi\Builders\JobConfigBuilder;

/**
 * @covers \Trivago\Rumi\Models\StagesCollection
 */
class StagesCollectionTest extends TestCase
{
    public function testGivenConfig_WhenNewCollectionCreated_ThenPossibleToIterate()
    {
        //given
        $config = [
            'stage1Name' => [],
        ];

        //when
        $jobConfigBuilder = $this->prophesize(JobConfigBuilder::class);
        $jobConfigBuilder->build([])->willReturn(new JobConfigCollection());

        $stagesCollection = new StagesCollection(
            $jobConfigBuilder->reveal(),
            $config
        );

        // then
        /** @var StageConfig $stage */
        foreach ($stagesCollection as $i => $stage) {
            $this->assertEquals('stage1Name', $stage->getName());
            $this->assertInstanceOf(JobConfigCollection::class, $stage->getJobs());
        }
    }

    public function testGivenStageInCollection_WhenRemoved_ThenItsNotApartOfCollection()
    {
        //given
        $config = [
            'stage1Name' => [],
        ];

        //when
        $jobConfigBuilder = $this->prophesize(JobConfigBuilder::class);
        $jobConfigBuilder->build([])->willReturn(new JobConfigCollection());

        $stagesCollection = new StagesCollection(
            $jobConfigBuilder->reveal(),
            $config
        );
        $stage = $stagesCollection->getIterator()->current();
        $stagesCollection->remove($stage);

        // then

        $this->assertEquals(0, $stagesCollection->getIterator()->count());
    }
}
