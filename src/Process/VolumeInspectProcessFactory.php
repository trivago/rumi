<?php
/**
 * @author jsacha
 *
 * @since 23/02/16 08:15
 */

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;

class VolumeInspectProcessFactory
{
    /**
     * @param $volumeName
     *
     * @return Process
     */
    public function getInspectProcess($volumeName)
    {
        $process = new Process(
            'docker volume inspect ' . escapeshellarg($volumeName)
        );

        return $process;
    }
}
