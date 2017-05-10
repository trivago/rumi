<?php
declare(strict_types=1);

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

namespace Trivago\Rumi\Models;

class JobConfigCollection implements \IteratorAggregate
{
    /**
     * @var JobConfig[]
     */
    private $jobs = [];

    public function add(JobConfig $job)
    {
        $this->jobs[] = $job;
    }

    /**
     *
     * @return \ArrayIterator|JobConfig[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->jobs);
    }

    /**
     * @param JobConfig $job
     *
     */
    public function remove(JobConfig $job)
    {
        foreach ($this->jobs as $k=>$j) {
            if ($j === $job) {
                unset($this->jobs[$k]);
                break;
            }
        }
    }

}
