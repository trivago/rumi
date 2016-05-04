<?php
/**
 * @author jsacha
 *
 * @since 12/12/15 23:38
 */

namespace jakubsacha\Rumi\Builders;

use jakubsacha\Rumi\Models\JobConfig;

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
