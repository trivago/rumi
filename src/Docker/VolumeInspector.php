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

namespace Trivago\Rumi\Docker;

use Trivago\Rumi\Process\VolumeInspectProcessFactory;

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
