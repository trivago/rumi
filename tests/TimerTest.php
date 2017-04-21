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
use PHPUnit\Framework\TestCase;

/**
 * @covers \Trivago\Rumi\Timer
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class TimerTest extends TestCase
{
    private $called = false;

    public function testTimer()
    {
        $cb = function () {
            usleep(300000);
            $this->called = true;
        };

        $result = \Trivago\Rumi\Timer::execute($cb);

        $this->assertTrue($this->called);
        $this->assertStringStartsWith('0.3', $result);
        $this->assertStringEndsWith('s', $result);
    }

    public function testCallbackArgumentsArePassedProperly()
    {
        $cb = function (int $arg1, bool $arg2) {
            $this->assertEquals(23, $arg1);
            $this->assertTrue($arg2);
        };

        \Trivago\Rumi\Timer::execute($cb, 23, true);
    }
}
