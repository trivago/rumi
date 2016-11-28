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

class RunningCommandCollection implements \IteratorAggregate, \ArrayAccess
{
    /**
     * @var RunningCommand[]
     */
    private $commands = [];

    /**
     * @param RunningCommand $command
     */
    public function add(RunningCommand $command){
        $this->commands[] = $command;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->commands);
    }

    public function offsetExists($offset)
    {
        return isset($this->commands[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->commands[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->commands[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->commands[$offset]);
    }
}