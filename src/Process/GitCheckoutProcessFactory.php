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

namespace Trivago\Rumi\Process;

use Symfony\Component\Process\Process;

class GitCheckoutProcessFactory
{
    protected $fetchCommand = 'GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" git fetch origin';

    /**
     * @param $repositoryUrl
     * @return GitProcess
     */
    public function getFullCloneProcess($repositoryUrl)
    {
        $process = new GitProcess(new Process('git init && git remote add origin ' . $repositoryUrl . ' && ' . $this->fetchCommand));
        $process->processFunctions()->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    /**
     * @return GitProcess
     */
    public function getFetchProcess()
    {
        $process = new GitProcess(new Process($this->fetchCommand));
        $process->processFunctions()->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    /**
     * @param $commitSha
     * @return GitProcess
     */
    public function getCheckoutCommitProcess($commitSha)
    {
        $process = new GitProcess(new Process('git reset --hard && git checkout ' . $commitSha));
        $process->processFunctions()->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    /**
     * @param $branch
     * @return GitProcess
     */
    public function getMergeProcess($branch)
    {
        $process = new GitProcess(new Process('git merge --no-edit ' . $branch));
        $process->processFunctions()->setTimeout(60)->setIdleTimeout(60);

        return $process;
    }
}
