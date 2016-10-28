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

/**
 * @author jsacha
 *
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

    protected function configure()
    {
        $this->addOption(
            self::CONFIG,
            self::CONFIG_SHORT,
            InputOption::VALUE_REQUIRED,
            'Configuration file to read',
            self::DEFAULT_CONFIG);
    }
}
