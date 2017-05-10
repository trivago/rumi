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

namespace Trivago\Rumi\Models;

use Trivago\Rumi\Builders\JobConfigBuilder;

class StagesCollection implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $stages = [];

    /**
     * @param JobConfigBuilder $jobConfigBuilder
     * @param array $stages
     */
    public function __construct(JobConfigBuilder $jobConfigBuilder, array $stages = [])
    {
        foreach ($stages as $stageName => $stageConfig)
        {
            $this->stages[] = new StageConfig(
                $stageName,
                $jobConfigBuilder->build($stageConfig)
            );
        }
    }

    /**
     *
     * @return \ArrayIterator|StageConfig[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->stages);
    }

    /**
     * @param StageConfig $stage
     *
     */
    public function remove(StageConfig $stage)
    {
        foreach($this->stages as $k => $s){
            if ($s === $stage) {
                unset ($this->stages[$k]);
                break;
            }
        }
    }
}
