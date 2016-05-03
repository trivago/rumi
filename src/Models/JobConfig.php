<?php
/**
 * @author jsacha
 *
 * @since 11/12/15 22:39
 */

namespace jakubsacha\Rumi\Models;

class JobConfig extends AbstractRunnableConfig
{
    /**
     * @var []MetricConfig
     */
    private $metrics;

    public function setMetrics($metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * @return []MetricConfig
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
