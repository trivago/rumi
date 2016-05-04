<?php
/**
 * @author jsacha
 * @since 29/04/16 13:47
 */

namespace jakubsacha\Rumi\Models;


class RunConfig
{
    private $stages;

    /**
     * RunConfig constructor.
     * @param $stages array
     */
    public function __construct($stages)
    {
        $this->stages = $stages;
    }

    /**
     * @return array
     */
    public function getStages()
    {
        return $this->stages;
    }
}