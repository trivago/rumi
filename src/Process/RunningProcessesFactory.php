<?php
/**
 * @author jsacha
 *
 * @since 21/02/16 22:32
 */

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;

class RunningProcessesFactory
{
    /**
     * @param $yamlPath
     * @param $tmpName
     * @param $ciImage
     *
     * @return Process
     */
    public function getJobStartProcess($yamlPath, $tmpName, $ciImage)
    {
        $process = new Process(
            'docker-compose -f '.$yamlPath.' run --name '.$tmpName.' '.$ciImage
        );
        $process->setTimeout(1200)->setIdleTimeout(1200);

        return $process;
    }

    /**
     * @param $yamlPath
     * @param $tmpName
     *
     * @return Process
     */
    public function getTearDownProcess($yamlPath, $tmpName)
    {
        $process = new Process(
            'docker rm -f '.$tmpName.';
            docker-compose -f '.$yamlPath.' rm --force;
            docker rm -f $(docker-compose -f '.$yamlPath.' ps -q)'
        );
        $process->setTimeout(300)->setIdleTimeout(300);

        return $process;
    }
}
