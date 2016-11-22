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

namespace Trivago\Rumi\Services;

use Symfony\Component\Yaml\Parser;
use Trivago\Rumi\Builders\JobConfigBuilder;
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Models\CacheConfig;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\StagesCollection;

class ConfigReader
{
    const CONFIG_FILE = '.rumi.yml';
    /**
     * @var JobConfigBuilder
     */
    private $jobConfigBuilder;

    /**
     * ConfigReader constructor.
     * @param JobConfigBuilder $jobConfigBuilder
     */
    public function __construct(JobConfigBuilder $jobConfigBuilder)
    {
        $this->jobConfigBuilder = $jobConfigBuilder;
    }

    /**
     * @param $workingDir
     * @param $configFile
     *
     * @throws \Exception
     *
     * @return RunConfig
     */
    public function getRunConfig($workingDir, $configFile)
    {
        $configFilePath = $workingDir . $configFile;

        if (!file_exists($configFilePath)) {
            throw new \Exception(
                'Required file \'' . $configFile . '\' does not exist',
                ReturnCodes::RUMI_YML_DOES_NOT_EXIST
            );
        }
        $parser = new Parser();

        $ciConfig = $parser->parse(file_get_contents($configFilePath));

        return new RunConfig(
            new StagesCollection(
                $this->jobConfigBuilder,
                $ciConfig['stages'] ?? []
            ),
            new CacheConfig($ciConfig['cache'] ?? []),
            $ciConfig['merge_branch'] ?? ""
        );
    }
}
