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

namespace Trivago\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;
use Trivago\Rumi\Models\JobConfigCollection;

class StageStartedEvent extends Event
{
    /**
     * @var
     */
    private $name;

    /**
     * @var JobConfigCollection
     */
    private $jobs;

    /**
     * StageStartedEvent constructor.
     *
     * @param $name
     * @param JobConfigCollection $jobs
     */
    public function __construct($name, JobConfigCollection $jobs)
    {
        $this->name = $name;
        $this->jobs = $jobs;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return JobConfigCollection
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
