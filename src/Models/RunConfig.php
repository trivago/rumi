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

class RunConfig
{
    /**
     * @var StagesCollection
     */
    private $stages;

    /**
     * @var CacheConfig
     */
    private $cache;

    /**
     * @var string
     */
    private $mergeBranch;

    /**
     * RunConfig constructor.
     *
     * @param $stages StagesCollection
     * @param $cache
     * @param $mergeBranch
     */
    public function __construct(
        StagesCollection $stages,
        CacheConfig $cache,
        string $mergeBranch
    ) {
        $this->stages = $stages;
        $this->cache = $cache;
        $this->mergeBranch = $mergeBranch;
    }

    /**
     * @return StagesCollection|StageConfig[]
     */
    public function getStagesCollection(): StagesCollection
    {
        return $this->stages;
    }

    /**
     * @return CacheConfig
     */
    public function getCache(): CacheConfig
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getMergeBranch(): string
    {
        return $this->mergeBranch;
    }
}
