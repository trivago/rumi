<?php
/*!
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
use Symfony\Component\Process\ProcessUtils;

class RunningProcessesFactory
{
    /**
     * @param string $yamlPath
     * @param string $tmpName
     * @param string $ciImage
     * @param int $timeout [optional]
     * @return Process
     */
    public function getJobStartProcess($yamlPath, $tmpName, $ciImage, $timeout = 1200)
    {
        $yamlPath = ProcessUtils::escapeArgument($yamlPath);
        $tmpName  = ProcessUtils::escapeArgument($tmpName);
        $ciImage  = ProcessUtils::escapeArgument($ciImage);

        return $this->buildProcess("docker-compose --file {$yamlPath} run -d --name {$tmpName} {$ciImage}", $timeout);
    }

    /**
     * @param string $yamlPath
     * @param string $tmpName
     * @param int $timeout [optional]
     * @return Process
     */
    public function getTearDownProcess($yamlPath, $tmpName, $timeout = 300)
    {
        $yamlPath = ProcessUtils::escapeArgument($yamlPath);
        $tmpName  = ProcessUtils::escapeArgument($tmpName);

        return $this->buildProcess(
            "docker rm --force {$tmpName};" .
            "docker-compose --file {$yamlPath} rm --force;" .
            "docker rm --force $(docker-compose --file {$yamlPath} ps -q);",
            $timeout
        );
    }

    /**
     * Build process for the given commandline and set timeout as well as idle timeout.
     *
     * @param string $commandline
     * @param int $timeout
     * @return Process
     */
    private function buildProcess($commandline, $timeout)
    {
        $process = new Process($commandline);
        $process->setTimeout($timeout);
        $process->setIdleTimeout($timeout);

        return $process;
    }
}
