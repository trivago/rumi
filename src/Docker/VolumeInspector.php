<?php
/**
 * @author jsacha
 *
 * @since 27/02/16 14:15
 */

namespace jakubsacha\Rumi\Docker;

use jakubsacha\Rumi\Process\VolumeInspectProcessFactory;

class VolumeInspector
{
    /**
     * @var VolumeInspectProcessFactory
     */
    private $volumeInspectorProcessFactory;

    /**
     * @param VolumeInspectProcessFactory $volumeInspectorProcessFactory
     */
    public function __construct(VolumeInspectProcessFactory $volumeInspectorProcessFactory)
    {
        $this->volumeInspectorProcessFactory = $volumeInspectorProcessFactory;
    }

    /**
     * @param $volumeName
     *
     * @return string
     */
    public function getVolumeRealPath($volumeName)
    {
        $process = $this->volumeInspectorProcessFactory->getInspectProcess($volumeName);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Can not read volume informations: '.$process->getErrorOutput());
        }
        $jsonOutput = json_decode($process->getOutput());

        if (!is_array($jsonOutput)) {
            throw new \RuntimeException('Docker response is not valid');
        }

        if ($jsonOutput[0]->Driver != 'local') {
            throw new \RuntimeException('Can use only local volumes');
        }

        return $jsonOutput[0]->Mountpoint.'/';
    }
}
