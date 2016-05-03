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
    private $metrics_config_builder;
    /**
     * @var ComposeParser
     */
    private $compose_handler;

    /**
     * @param MetricConfigBuilder $metrics_config_builder
     * @param ComposeParser       $compose_handler
     */
    public function __construct(MetricConfigBuilder $metrics_config_builder,
                                ComposeParser $compose_handler)
    {
        $this->metrics_config_builder = $metrics_config_builder;
        $this->compose_handler = $compose_handler;
    }

    public function build($aStageConfig)
    {
        $aJobs = [];
        foreach ($aStageConfig as $sJobName => $aJobConfig) {
            $oJob = new JobConfig(
                $sJobName,
                $this->compose_handler->parseComposePart(!empty($aJobConfig['docker']) ? $aJobConfig['docker'] : null),
                !empty($aJobConfig['ci_image']) ? $aJobConfig['ci_image'] : null,
                !empty($aJobConfig['entrypoint']) ? $aJobConfig['entrypoint'] : null,
                !empty($aJobConfig['commands']) ? $aJobConfig['commands'] : null
            );

            if (!empty($aJobConfig['metrics'])) {
                $oJob->setMetrics($this->metrics_config_builder->build($aJobConfig['metrics']));
            }

            $aJobs[] = $oJob;
        }

        return $aJobs;
    }
}
