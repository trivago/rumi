<?php
/**
 * @author jsacha
 *
 * @since 18/01/16 08:16
 */

namespace jakubsacha\Rumi;

class Timer
{
    public static function execute(callable $cb)
    {
        $time = microtime(true);
        $cb();

        return number_format(microtime(true) - $time, 3, '.', '') . 's';
    }
}
