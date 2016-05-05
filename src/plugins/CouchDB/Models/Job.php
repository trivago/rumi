<?php
/**
 * @author jsacha
 *
 * @since 05/05/16 21:14
 */

namespace jakubsacha\Rumi\Plugins\CouchDB\Models;

class Job
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $output;

    /**
     * Job constructor.
     */
    public function __construct($name, $status)
    {
        $this->name = $name;
        $this->status = $status;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
