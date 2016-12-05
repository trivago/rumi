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

use Symfony\Component\Process\Exception\ProcessTimedOutException;

class RunningCommandCollection implements \ArrayAccess, \Iterator
{
    /**
     * @var RunningCommand[]
     */
    private $commands = [];

    /**
     * @var RunningCommand[]
     */
    private $launchedProcesses = [];

    /**
     * @var int
     */
    private $ptr = null;

    /**
     * @var int
     */
    private $maxParallelProcesses = 5;

    /**
     * @param RunningCommand $command
     */
    public function add(RunningCommand $command)
    {
        $this->commands[] = $command;
    }

    public function startProcesses()
    {
        foreach ($this->commands as $command) {
            $command->start();
            // add random delay to put less stress on the docker daemon
            usleep(rand(100000, 500000));
        }
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
        if (false === ($value instanceof RunningCommand)) {
            throw new \InvalidArgumentException('Value is not a instance of ' . RunningCommand::class);
        }

        $this->commands[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->commands[$offset]);
    }

    public function current()
    {
        $command = $this->launchedProcesses[$this->ptr];
        unset($this->launchedProcesses[$this->ptr]);

        if (!empty($this->commands)) {
            $this->launchedProcesses[] = $this->shiftCommand();
        }

//        $command->tearDown(); // we started the command internally should we stop the process then internally too?

        return $command;
    }

    public function next()
    {
        while ($this->valid()) {
            foreach ($this->launchedProcesses as $key => $command) {
                if (!$command->isRunning()) {
                    $this->ptr = $key;
                    return;
                }
                try {
                    $command->checkTimeout();
                } catch (ProcessTimedOutException $e) {
                    $this->ptr = $key;
                    return;
                }
            }
        }
    }

    public function key()
    {
        return $this->ptr;
    }

    public function valid()
    {
        return count($this->launchedProcesses) > 0;
    }

    public function rewind()
    {
        $this->launchedProcesses = [];
        reset($this->commands);
        for ($i = 0; $i < $this->maxParallelProcesses && !empty($this->commands); ++$i) {
            $this->launchedProcesses[] = $this->shiftCommand();
        }
        $this->ptr = key($this->launchedProcesses);
    }

    private function shiftCommand()
    {
        $command = array_shift($this->commands); // LIFO
        if (null !== $command) {
            // $command->start(); // start command here
        }
        return $command;
    }
}
