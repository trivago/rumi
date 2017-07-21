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

class RunningCommandCollectionTest extends TestCase
{

    public function testGivenRunningProcess_WhenCollectionCreated_ThenItContains()
    {
        // given
        $process = $this->prophesize(RunningCommandInterface::class)->reveal();

        // when
        $collection = new RunningCommandCollection();
        $collection->add($process);

        // then

        $this->assertContains($process, $collection->getIterator()->getArrayCopy());
    }

    public function testGivenRunningProcesses_WhenCollectionCreated_ThenIteratorWorks()
    {
        //given
        $collection = new RunningCommandCollection();
        $collection->add($this->prophesize(RunningCommandInterface::class)->reveal());
        $collection->add($this->prophesize(RunningCommandInterface::class)->reveal());
        $collection->add($this->prophesize(RunningCommandInterface::class)->reveal());
        $collection->add($this->prophesize(RunningCommandInterface::class)->reveal());

        // when
        $collection->offsetSet(4, $this->prophesize(RunningCommandInterface::class)->reveal());

        $collection->add($this->prophesize(RunningCommandInterface::class)->reveal());
        $collection->offsetUnset(5);

        // then
        $this->assertInstanceOf(RunningCommandInterface::class, $collection->offsetGet(0));
        $this->assertTrue($collection->offsetExists(0));
        $this->assertEquals(5, count($collection->getIterator()));
    }
}
