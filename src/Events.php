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

namespace Trivago\Rumi;

interface Events
{
    const RUN_STARTED = 'run_started';
    const RUN_FINISHED = 'run_completed';

    const STAGE_STARTED = 'stage_started';
    const STAGE_FINISHED = 'stage_finished';

    const JOB_STARTED = 'job_started';
    const JOB_FINISHED = 'job_finished';

    const GIT_CLONE_STARTED = 'git_clone_process_started';
}
