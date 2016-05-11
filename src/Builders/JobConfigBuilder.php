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

use Trivago\Rumi\Models\JobConfig;

class JobConfigBuilder
{
    /**
     * @var MetricConfigBuilder
     */
    private $metricsConfigBuilder;
    /**
     * @var ComposeParser
     */
    private $composeHandler;

    /**
     * @param MetricConfigBuilder $metrics_config_builder
     * @param ComposeParser       $compose_handler
     */
    public function __construct(MetricConfigBuilder $metrics_config_builder,
                                ComposeParser $compose_handler)
    {
        $this->metricsConfigBuilder = $metrics_config_builder;
        $this->composeHandler = $compose_handler;
    }

    public function build($stageConfig)
    {
        if (empty($stageConfig)) {
            return [];
        }
        $jobs = [];
        foreach ($stageConfig as $jobName => $jobConfig) {
            $job = new JobConfig(
                $jobName,
                $this->composeHandler->parseComposePart(!empty($jobConfig['docker']) ? $jobConfig['docker'] : null),
                !empty($jobConfig['ci_image']) ? $jobConfig['ci_image'] : null,
                !empty($jobConfig['entrypoint']) ? $jobConfig['entrypoint'] : null,
                !empty($jobConfig['commands']) ? $jobConfig['commands'] : null
            );

            if (!empty($jobConfig['metrics'])) {
                $job->setMetrics($this->metricsConfigBuilder->build($jobConfig['metrics']));
            }

            $jobs[] = $job;
        }

        return $jobs;
    }
}
