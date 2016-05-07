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

use Trivago\Rumi\Models\RunConfig;

/**
 * @covers \Trivago\Rumi\Events\RunStartedEvent
 */
class RunStartedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenConfig_WhenNewInstanceCreated_ThenGetterWorks()
    {
        //given
        $runConfig = new RunConfig(['abc']);

        // when
        $event = new RunStartedEvent($runConfig);

        // then
        $this->assertEquals($runConfig, $event->getRunConfig());
    }
}
