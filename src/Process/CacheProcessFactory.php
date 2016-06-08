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

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;

class CacheProcessFactory
{
    /**
     * @param $directory
     * @param $cacheDestinationDirectory
     *
     * @return Process
     */
    public function getCacheStoreProcess($directory, $cacheDestinationDirectory)
    {
        $process = new Process('
                (
                    flock -x 200 || exit 1;
                    rsync --delete -axH '.$directory.'/ '.$cacheDestinationDirectory.'/data/'.$directory.'
                ) 200>'.$cacheDestinationDirectory.'/.rsync.lock');
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    /**
     * @param $cacheDir
     *
     * @return Process
     */
    public function getCreateCacheDirectoryProcess($cacheDir)
    {
        return new Process('mkdir -p '.$cacheDir.'/data/');
    }

    /**
     * @param string $cacheDir
     * @param string $lockDir
     *
     * @return Process
     */
    public function getCacheRestoreProcess($cacheDir, $lockDir)
    {
        $process = new Process('
                (
                    flock -x 200 || exit 1;
                    rsync --delete -axH '.$cacheDir.' . ;
                ) 200>'.$lockDir.'/.rsync.lock');

        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }
}
