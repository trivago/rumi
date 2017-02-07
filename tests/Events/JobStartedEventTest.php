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

/**
 * @covers \Trivago\Rumi\Events\JobStartedEvent
 */
class JobStartedEventTest extends TestCase
{
    public function testGivenName_WhenNewInstanceCreated_ThenGetterWorks()
    {
        //given
        $name = 'name';

        // when
        $event = new JobStartedEvent($name);

        // then
        $this->assertEquals($name, $event->getName());
    }
}
