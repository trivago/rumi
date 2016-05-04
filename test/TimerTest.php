<?php

/**
 * @author jsacha
 *
 * @since 20/02/16 17:06
 */
class TimerTest extends PHPUnit_Framework_TestCase
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
}
