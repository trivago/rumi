<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 19:58
 */

namespace jakubsacha\Rumi\Events;

use jakubsacha\Rumi\Models\RunConfig;
use Symfony\Component\EventDispatcher\Event;

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
