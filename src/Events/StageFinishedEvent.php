<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 20:13
 */

namespace jakubsacha\Rumi\Events;

class StageFinishedEvent extends AbstractFinishedEvent
{
    /**
     * @var
     */
    private $name;

    public function __construct($status, $name)
    {
        parent::__construct($status);

        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
