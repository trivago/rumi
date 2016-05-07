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

use Trivago\Rumi\Models\MetricConfig;

class MetricConfigBuilder
{
    /**
     * @var ComposeParser
     */
    private $composeHandler;

    /**
     * @param ComposeParser $composeHandler
     */
    public function __construct(ComposeParser $composeHandler)
    {
        $this->composeHandler = $composeHandler;
    }

    /**
     * @param $composeConfig
     *
     * @throws \Exception
     *
     * @return \Trivago\Rumi\Models\MetricConfig[]
     */
    public function build($composeConfig)
    {
        $metrics = [];

        foreach ($composeConfig as $name => $config) {
            $metrics[] = new MetricConfig(
                $name,
                $this->composeHandler->parseComposePart(!empty($config['docker']) ? $config['docker'] : null),
                !empty($config['ci_image']) ? $config['ci_image'] : null,
                !empty($config['entrypoint']) ? $config['entrypoint'] : null,
                !empty($config['commands']) ? $config['commands'] : null
            );
        }

        return $metrics;
    }
}
