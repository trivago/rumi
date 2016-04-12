<?php
/**
 * @author jsacha
 * @since 01/03/16 22:57
 */

namespace jakubsacha\Rumi\Models;

abstract class AbstractRunnableConfig
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
    )
    {
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
        if (count($this->getCommands()) == 0)
        {
            return null;
        }

        return implode(" ;", $this->getCommands());
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
     * from docker-compose part is used
     *
     * @return string
     */
    public function getCiContainer()
    {
        // if ci_image is not defined, use first defined container
        if (empty($this->ci_container))
        {
            return current(array_keys($this->getDockerCompose()));
        }

        return $this->ci_container;
    }

    /**
     * Returns entry point used to spin up container
     *
     * @return string|null
     */
    public function getEntryPoint()
    {
        return $this->entry_point;
    }
}