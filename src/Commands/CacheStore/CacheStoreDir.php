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

namespace Trivago\Rumi\Commands\CacheStore;

use Trivago\Rumi\Process\CacheProcessFactory;
use Trivago\Rumi\Timer;

class CacheStoreDir
{
    /**
     * @var CacheProcessFactory
     */
    private $cacheProcessFactory;

    /**
     * @param CacheProcessFactory $cacheProcessFactory
     */
    public function __construct(CacheProcessFactory $cacheProcessFactory)
    {
        $this->cacheProcessFactory = $cacheProcessFactory;
    }

    public function store($source, $cacheDir)
    {
        if (!file_exists($source)) {
            return 'Source directory: '.$source.' does not exist';
        }

        $process = $this
            ->cacheProcessFactory
            ->getCacheStoreProcess($source, $cacheDir);

        $time = Timer::execute(function () use ($process) {
            $process->run();
        });

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getOutput().$process->getErrorOutput());
        }

        return 'Storing cache for: '.$source.'... '.$time;
    }
}
