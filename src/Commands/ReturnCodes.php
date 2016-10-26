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

namespace Trivago\Rumi\Commands;

interface ReturnCodes
{
    const SUCCESS = 0;
    const FAILED = 1;

    const RUMI_YML_DOES_NOT_EXIST = 2;
    const VOLUME_MOUNT_FROM_FILESYSTEM = 3;
    const FAILED_DUE_TO_REPOSITORY_PERMISSIONS = 4;
}
