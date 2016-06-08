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
use Trivago\Rumi\Commands\ReturnCodes;
use Trivago\Rumi\Models\RunConfig;

class ConfigReader
{
    const CONFIG_FILE = '.rumi.yml';

    /**
     * @param $workingDir
     *
     * @throws \Exception
     *
     * @return RunConfig
     */
    public function getConfig($workingDir)
    {
        $configFilePath = $workingDir.self::CONFIG_FILE;

        if (!file_exists($configFilePath)) {
            throw new \Exception(
                'Required file \''.self::CONFIG_FILE.'\' does not exist',
                ReturnCodes::RUMI_YML_DOES_NOT_EXIST
            );
        }
        $parser = new Parser();

        $ciConfig = $parser->parse(file_get_contents($configFilePath));

        return new RunConfig(
            !empty($ciConfig['stages']) ? $ciConfig['stages'] : [],
            !empty($ciConfig['cache']) ? $ciConfig['cache'] : [],
            !empty($ciConfig['merge_branch']) ? $ciConfig['merge_branch'] : null
        );
    }
}
