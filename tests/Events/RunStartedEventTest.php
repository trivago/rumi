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

namespace Trivago\Rumi\Events;

use PHPUnit\Framework\TestCase;
use Trivago\Rumi\Builders\JobConfigBuilder;
use Trivago\Rumi\Models\CacheConfig;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\StagesCollection;

/**
 * @covers \Trivago\Rumi\Events\RunStartedEvent
 */
class RunStartedEventTest extends TestCase
{
    public function testGivenConfig_WhenNewInstanceCreated_ThenGetterWorks()
    {
        //given
        $jobConfigBuilder = $this->prophesize(JobConfigBuilder::class);
        $jobConfigBuilder->build(['config'])->willReturn(new JobConfigCollection());

        $runConfig = new RunConfig(
            new StagesCollection(
                $jobConfigBuilder->reveal(),
                ['abc'=>['config']]
            ),
            new CacheConfig(['cache']),
            'merge_branch'
        );

        // when
        $event = new RunStartedEvent($runConfig);

        // then
        $this->assertEquals($runConfig, $event->getRunConfig());
    }
}
