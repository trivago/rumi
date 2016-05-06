<?php
/**
 * @author jsacha
 *
 * @since 05/05/16 21:51
 */

namespace Trivago\Rumi\Plugins\CouchDB\Models;

class Run
{
    /**
     * @var Stage[]
     */
    private $stages;

    /**
     * @var string
     */
    private $commit;

    /**
     * Run constructor.
     *
     * @param $commit
     */
    public function __construct($commit)
    {
        $this->commit = $commit;
    }

    public function addStage($stage)
    {
        $this->stages[] = $stage;
    }

    /**
     * @return Stage[]
     */
    public function getStages()
    {
        return $this->stages;
    }

    /**
     * @return string
     */
    public function getCommit()
    {
        return $this->commit;
    }
}
