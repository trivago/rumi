<?php
/**
 * @author jsacha
 *
 * @since 23/02/16 10:11
 */

namespace jakubsacha\Rumi\Process;

use Symfony\Component\Process\Process;

class CacheProcessFactory
{
    /**
     * @param $sDirectory
     * @param $sCacheDestinationDirectory
     *
     * @return Process
     */
    public function getCacheStoreProcess($sDirectory, $sCacheDestinationDirectory)
    {
        $oProcess = new Process('
                (
                    flock -x 200 || exit 1;
                    rsync --delete -axH ' . $sDirectory . '/ ' . $sCacheDestinationDirectory . '/data/' . $sDirectory . '
                ) 200>' . $sCacheDestinationDirectory . '/.rsync.lock');
        $oProcess->setTimeout(600)->setIdleTimeout(600);

        return $oProcess;
    }

    /**
     * @param $sCacheDir
     *
     * @return Process
     */
    public function getCreateCacheDirectoryProcess($sCacheDir)
    {
        return new Process('mkdir -p ' . $sCacheDir . '/data/');
    }

    /**
     * @param string $_sCacheDir
     * @param string $_sLockDir
     *
     * @return Process
     */
    public function getCacheRestoreProcess($_sCacheDir, $_sLockDir)
    {
        $oProcess = new Process('
                (
                    flock -x 200 || exit 1;
                    rsync --delete -axH ' . $_sCacheDir . ' . ;
                ) 200>' . $_sLockDir . '/.rsync.lock');

        $oProcess->setTimeout(600)->setIdleTimeout(600);

        return $oProcess;
    }
}
