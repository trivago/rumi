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
    private $oVolumeInspectFactory;

    /**
     * @param VolumeInspectProcessFactory $oVolumeInspectFactory
     */
    public function __construct(VolumeInspectProcessFactory $oVolumeInspectFactory)
    {
        $this->oVolumeInspectFactory = $oVolumeInspectFactory;
    }

    /**
     * @param $sVolumeName
     *
     * @return string
     */
    public function getVolumeRealPath($sVolumeName)
    {
        $oProcess = $this->oVolumeInspectFactory->getInspectProcess($sVolumeName);
        $oProcess->run();

        if (!$oProcess->isSuccessful()) {
            throw new \RuntimeException('Can not read volume informations: ' . $oProcess->getErrorOutput());
        }
        $aJsonOutput = json_decode($oProcess->getOutput());

        if (!is_array($aJsonOutput)) {
            throw new \RuntimeException('Docker response is not valid');
        }

        if ($aJsonOutput[0]->Driver != 'local') {
            throw new \RuntimeException('Can use only local volumes');
        }

        return $aJsonOutput[0]->Mountpoint . '/';
    }
}
