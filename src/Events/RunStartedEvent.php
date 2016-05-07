<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 19:58
 */

namespace Trivago\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;
use Trivago\Rumi\Models\RunConfig;

class RunStartedEvent extends Event
{
    /**
     * @var RunConfig
     */
    private $runConfig;

    /**
     * RunStartedEvent constructor.
     *
     * @param RunConfig $runConfig
     */
    public function __construct(RunConfig $runConfig)
    {
        $this->runConfig = $runConfig;
    }

    /**
     * @return RunConfig
     */
    public function getRunConfig()
    {
        return $this->runConfig;
    }
}
