<?php
/**
 * @author jsacha
 *
 * @since 21/02/16 22:32
 */

namespace jakubsacha\Rumi\Process;

use Symfony\Component\Process\Process;

class RunningProcessesFactory
{
    /**
     * @param $sYamlPath
     * @param $sTmpName
     * @param $sCiImage
     *
     * @return Process
     */
    public function getJobStartProcess($sYamlPath, $sTmpName, $sCiImage)
    {
        $oProcess = new Process(
            'docker-compose -f ' . $sYamlPath . ' run --name ' . $sTmpName . ' ' . $sCiImage
        );
        $oProcess->setTimeout(1200)->setIdleTimeout(1200);

        return $oProcess;
    }

    /**
     * @param $sYamlPath
     * @param $sTmpName
     *
     * @return Process
     */
    public function getTearDownProcess($sYamlPath, $sTmpName)
    {
        $oProcess = new Process(
            'docker rm -f ' . $sTmpName . ';
            docker-compose -f ' . $sYamlPath . ' rm --force;
            docker rm -f $(docker-compose -f ' . $sYamlPath . ' ps -q)'
        );
        $oProcess->setTimeout(300)->setIdleTimeout(300);

        return $oProcess;
    }
}
