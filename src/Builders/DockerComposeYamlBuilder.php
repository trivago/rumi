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

namespace Trivago\Rumi\Builders;

use Symfony\Component\Yaml\Dumper;
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Docker\VolumeInspector;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\VCSInfo\VCSInfoInterface;

class DockerComposeYamlBuilder
{
    const DEFAULT_SHELL = 'sh';

    const GIT_COMMIT = 'GIT_COMMIT';
    const GIT_BRANCH = 'GIT_BRANCH';
    const GIT_URL = 'GIT_URL';
    /**
     * @var VolumeInspector
     */
    private $volumeInspector;

    public function __construct(VolumeInspector $volumeInspector)
    {
        $this->volumeInspector = $volumeInspector;
    }

    /**
     * Builds docker-compose.yml file based on JobConfig entity.
     *
     * @param JobConfig        $stageConfig
     * @param VCSInfoInterface $VCSInfo
     * @param string           $volume      docker volume or path to project files
     *
     * @return string
     */
    public function build(JobConfig $stageConfig, VCSInfoInterface $VCSInfo, $volume)
    {
        $composeConfig = $stageConfig->getDockerCompose();

        $ciContainerName = $stageConfig->getCiContainer();
        $command = $stageConfig->getCommandsAsString();

        $entryPoint = $stageConfig->getEntryPoint();

        // if the entry point is specified, use it
        if (!empty($entryPoint)) {
            $composeConfig[$ciContainerName]['entrypoint'] = $entryPoint;
        }
        // otherwise we check do we have any commands specified, in case yes - we use default "sh" shell
        elseif (!empty($command)) {
            $composeConfig[$ciContainerName]['entrypoint'] = self::DEFAULT_SHELL;
        }

        // if we have any commands specified in yaml file, we put them into command part of compose file
        if (!empty($command)) {
            $composeConfig[$ciContainerName]['command'] =
                // escape $ to support docker syntax
                ['-c', str_replace('$', '$$', $command)];
        }

        // add environment variables describing commit
        if (!isset($composeConfig[$ciContainerName]['environment'])) {
            $composeConfig[$ciContainerName]['environment'] = [];
        }
        $composeConfig[$ciContainerName]['environment'][self::GIT_COMMIT] = $VCSInfo->getCommitId();
        $composeConfig[$ciContainerName]['environment'][self::GIT_BRANCH] = $VCSInfo->getBranch();
        $composeConfig[$ciContainerName]['environment'][self::GIT_URL] = $VCSInfo->getUrl();

        // remove all port mappings (we do not want to expose anything) and fix volumes settings
        foreach ($composeConfig as $container => $settings) {
            if (!empty($composeConfig[$container]['ports'])) {
                unset($composeConfig[$container]['ports']);
            }

            if (!isset($composeConfig[$container]['volumes'])) {
                continue;
            }

            // performance flags
            $composeConfig[$container]['cpu_shares'] = 2;

            foreach ($composeConfig[$container]['volumes'] as $volumeKey => $volumeSpec) {
                $this->validateVolume($volumeSpec);

                $composeConfig[$container]['volumes'][$volumeKey] = $this->handlePathsReplacement($volume, $volumeSpec);
            }
        }

        return $this->dumpFile($composeConfig);
    }

    /**
     * @param $parsedDockerCompose
     *
     * @return string file path
     */
    protected function dumpFile($parsedDockerCompose)
    {
        $tempTestDirectory = tempnam(sys_get_temp_dir(), 'RUNNER') . md5(microtime()) . '_d';
        usleep(1);
        mkdir($tempTestDirectory);

        $dumper = new Dumper();
        file_put_contents(
            $tempTestDirectory . '/docker-compose.yml',
            $dumper->dump($parsedDockerCompose, 10)
        );

        return $tempTestDirectory . '/docker-compose.yml';
    }

    /**
     * @param $volume
     *
     * @return bool
     */
    protected function isDockerVolume($volume)
    {
        return $volume[0] != '/';
    }

    /**
     * @param $volumeName
     * @param $volumeSpecification
     *
     * @return mixed
     */
    public function handlePathsReplacement($volumeName, $volumeSpecification)
    {
        // if the full volume is mounted, there is no magic needed
        if (strpos($volumeSpecification, './') !== 0) {
            return str_replace('.:', $volumeName . ':', $volumeSpecification);
        }

        // if we use systempath instead of docker volume
        if (!$this->isDockerVolume($volumeName)) {
            return str_replace('./', rtrim($volumeName, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $volumeSpecification);
        }

        // we need to get real docker volume path and use it
        return str_replace('./', $this->volumeInspector->getVolumeRealPath($volumeName), $volumeSpecification);
    }

    /**
     * @param $volumeSpec
     *
     * @throws \Exception
     */
    private function validateVolume($volumeSpec)
    {
        if (substr($volumeSpec, 0, 1) == '/' or substr($volumeSpec, 0, 1) == '~') {
            throw new \Exception(
                'Volume configuration: \'' . $volumeSpec . '\' is forbidden.',
                ReturnCodes::VOLUME_MOUNT_FROM_FILESYSTEM);
        }
    }
}
