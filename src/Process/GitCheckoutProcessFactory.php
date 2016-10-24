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

    public function getFullCloneProcess($repositoryUrl)
    {
        $process = new GitProcess(
            'git init && git remote add origin ' . $repositoryUrl . ' && ' . $this->fetchCommand
        );
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    public function getFetchProcess()
    {
        $process = new GitProcess($this->fetchCommand);
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    public function getCheckoutCommitProcess($commitSha)
    {
        $process = new Process(
            'git reset --hard && git checkout ' . $commitSha
        );
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }

    public function getMergeProcess($branch)
    {
        $process = new Process(
            'git merge --no-edit ' . $branch
        );
        $process->setTimeout(60)->setIdleTimeout(60);

        return $process;
    }
}
