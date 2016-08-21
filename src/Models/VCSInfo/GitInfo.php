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

namespace Trivago\Rumi\Models\VCSInfo;

class GitInfo implements VCSInfoInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $commitId;

    /**
     * @var string
     */
    private $branch;

    /**
     * @param $url
     * @param $commitId
     * @param $branch
     */
    public function __construct($url, $commitId, $branch)
    {
        $this->url = $url;
        $this->commitId = $commitId;
        $this->branch = $branch;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getCommitId()
    {
        return $this->commitId;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }
}
