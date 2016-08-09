<?php
/**
 * @author jsacha
 * @since 06/08/16 19:54
 */

namespace Trivago\Rumi\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class CommandAbstract extends Command
{
    const CONFIG = 'config';
    const CONFIG_SHORT = 'c';

    const DEFAULT_CONFIG = '.rumi.yml';

    protected function configure(){
        $this->addOption(
            self::CONFIG,
            self::CONFIG_SHORT,
            InputOption::VALUE_REQUIRED,
            'Configuration file to read',
            self::DEFAULT_CONFIG);
    }
}
