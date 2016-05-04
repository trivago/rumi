<?php
/**
 * @author jsacha
 *
 * @since 23/02/16 10:11
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
                    rsync --delete -axH ' . $directory . '/ ' . $cacheDestinationDirectory . '/data/' . $directory . '
                ) 200>' . $cacheDestinationDirectory . '/.rsync.lock');
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
        return new Process('mkdir -p ' . $cacheDir . '/data/');
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
                    rsync --delete -axH ' . $cacheDir . ' . ;
                ) 200>' . $lockDir . '/.rsync.lock');

        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }
}
