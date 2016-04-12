<?php

namespace jakubsacha\Rumi\Builders;

use jakubsacha\Rumi\Models\MetricConfig;

class MetricConfigBuilder
{
    /**
     * @var ComposeParser
     */
    private $compose_handler;

    /**
     * @param ComposeParser $compose_handler
     */
    public function __construct(ComposeParser $compose_handler)
    {
        $this->compose_handler = $compose_handler;
    }

    /**
     * @param $compose_config
     * @return \jakubsacha\Rumi\Models\MetricConfig[]
     * @throws \Exception
     */
    public function build($compose_config)
    {
        $aMetrics = [];

        foreach ($compose_config as $name => $config){
            $aMetrics[] = new MetricConfig(
                $name,
                $this->compose_handler->parseComposePart(!empty($config['docker']) ? $config['docker'] : null),
                !empty($config['ci_image']) ? $config['ci_image'] : null,
                !empty($config['entrypoint']) ? $config['entrypoint'] : null,
                !empty($config['commands']) ? $config['commands'] : null
            );
        }

        return $aMetrics;
    }
}