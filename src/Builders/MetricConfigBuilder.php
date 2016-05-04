<?php

namespace jakubsacha\Rumi\Builders;

use jakubsacha\Rumi\Models\MetricConfig;

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
     * @return \jakubsacha\Rumi\Models\MetricConfig[]
     *
     * @throws \Exception
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
