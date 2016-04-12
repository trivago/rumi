<?php
/**
 * @author jsacha
 * @since 23/02/16 08:15
 */

namespace jakubsacha\Rumi\Process;


use Symfony\Component\Process\Process;

class VolumeInspectProcessFactory
{
    /**
     * @param $sVolume
     * @return Process
     */
    public function getInspectProcess($sVolume)
    {
        $_oProcess = new Process(
            'docker volume inspect '.escapeshellarg($sVolume)
        );

        return $_oProcess;
    }
}