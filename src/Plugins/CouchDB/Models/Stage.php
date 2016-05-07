<?php

/*
 * Copyright 2016 trivago GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
