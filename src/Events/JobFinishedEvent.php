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

namespace Trivago\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;

class JobFinishedEvent extends Event
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    const STATUS_ABORTED = 'ABORTED';

    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $output;

    /**
     * JobFinishedEvent constructor.
     *
     * @param $status
     * @param $name
     * @param $output
     */
    public function __construct($status, $name, $output)
    {
        $this->status = $status;
        $this->name = $name;
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
