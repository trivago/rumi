<?php
/**
 * @author jsacha
 *
 * @since 05/05/16 21:13
 */

namespace Trivago\Rumi\Plugins\CouchDB\Models;

class Stage
{
    /**
     * @var Job[]
     */
    private $jobs = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addJob(Job $job)
    {
        $this->jobs[] = $job;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Job
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param $name
     *
     * @return null|Job
     */
    public function getJob($name)
    {
        foreach ($this->jobs as $job) {
            if ($job->getName() == $name) {
                return $job;
            }
        }

        return;
    }
}
