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

class JobConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $docker_compose;

    /**
     * @var array
     */
    protected $commands;

    /**
     * @var string|null
     */
    protected $ci_container;

    /**
     * @var string|null
     */
    protected $entry_point;

    /**
     * @param $name
     * @param $docker_compose
     * @param $ci_container
     * @param $entrypoint
     * @param $commands
     */
    public function __construct(
        $name, $docker_compose, $ci_container, $entrypoint, $commands
    ) {
        $this->name = $name;
        $this->docker_compose = $docker_compose;
        $this->ci_container = $ci_container;
        $this->entry_point = $entrypoint;
        $this->commands = $commands;
    }

    /**
     * @return array
     */
    public function getDockerCompose()
    {
        return $this->docker_compose;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    public function getCommandsAsString()
    {
        if (count($this->getCommands()) == 0) {
            return;
        }

        $commandsPrints = [];
        foreach ($this->getCommands() as $command) {
            $commandsPrints[] = 'echo "Executing command: '.escapeshellcmd($command).'"';
            $commandsPrints[] = $command;
        }

        return implode(' && ', $commandsPrints);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns container used for ci execution. If it's not defined, first container
     * from docker-compose part is used.
     *
     * @return string
     */
    public function getCiContainer()
    {
        // if ci_image is not defined, use first defined container
        if (empty($this->ci_container)) {
            return current(array_keys($this->getDockerCompose()));
        }

        return $this->ci_container;
    }

    /**
     * Returns entry point used to spin up container.
     *
     * @return string|null
     */
    public function getEntryPoint()
    {
        return $this->entry_point;
    }
}
