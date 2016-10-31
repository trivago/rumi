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

namespace Trivago\Rumi\Validators;

use Symfony\Component\Process\Process;
use Trivago\Rumi\Commands\ReturnCodes;

class GitCheckoutValidator
{
    /**
     * @param Process $process
     *
     * @throws \Exception
     */
    public function checkStatus(Process $process)
    {
        if ($process->isSuccessful()) {
            return;
        }

        if ($process->getExitCode() == 128 && preg_match('/permission/i', $process->getErrorOutput())) {
            throw new \Exception(
              'Rumi has no permissions to your repository',
               ReturnCodes::FAILED_DUE_TO_REPOSITORY_PERMISSIONS
            );
        }

        throw new \Exception(
            $process->getErrorOutput(),
            ReturnCodes::FAILED
        );
    }
}
