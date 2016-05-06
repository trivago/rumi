<?php
/**
 * @author jsacha
 *
 * @since 20/02/16 19:57
 */

namespace Trivago\Rumi\Models;

class RunnableConfig extends AbstractRunnableConfig
{
}
/**
 * @covers Trivago\Rumi\Models\AbstractRunnableConfig
 */
class AbstractRunnableConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNoCiContainerDefined_WhenGetCiContainerAsStringCalled_ThenFirstContainerIsUsed()
    {
        $job = new RunnableConfig(
            'name',
            ['www' => [], 'second' => []],
            null,
            null,
            null
        );

        $this->assertEquals('www', $job->getCiContainer());
    }

    public function testGivenCiContainerIsDefined_WhenGetCiContainerAsStringCalled_ThenDefinedContainerIsUsed()
    {
        $job = new RunnableConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            null,
            null
        );

        $this->assertEquals('second', $job->getCiContainer());
    }

    public function testGivenParamsArePassed_WhenNewObjectCreated_ThenGettersAreFine()
    {
        $job = new RunnableConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            'third',
            ['fourth', 'sixth']
        );

        $this->assertEquals('name', $job->getName());
        $this->assertEquals('fourth ;sixth', $job->getCommandsAsString());
        $this->assertEquals(['fourth', 'sixth'], $job->getCommands());
        $this->assertEquals(['www' => [], 'second' => []], $job->getDockerCompose());
        $this->assertEquals('third', $job->getEntryPoint());
    }

    public function testGivenEmptyCommands_WhenNewObjectCreated_ThenGetCommandAsStringReturnsNull()
    {
        $job = new RunnableConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            'third',
            null
        );

        $this->assertEquals('', $job->getCommandsAsString());
    }
}
