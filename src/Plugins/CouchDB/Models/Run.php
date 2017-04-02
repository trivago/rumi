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
     * @var string
     */
    private $branch;

    /**
     * @var string
     */
    private $repository_url;

    /**
     * @var string
     */
    private $build_url;

    /**
     * Run constructor.
     *
     * @param $commit
     * @param $branch
     * @param $repository_url
     */
    public function __construct($commit, $branch, $repository_url)
    {
        $this->commit = $commit;
        $this->timestamp = time();
        $this->branch = $branch;
        $this->repository_url = $repository_url;
        $this->build_url = getenv('BUILD_URL'); // jenkins specific
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

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }

    /**
     * @return string
     */
    public function getRepositoryUrl(): string
    {
        return $this->repository_url;
    }

    /**
     * @return string
     */
    public function getBuildUrl(): string
    {
        return $this->build_url;
    }

}
