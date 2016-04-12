<?php
/**
 * @author jsacha
 * @since 20/02/16 19:57
 */

namespace jakubsacha\Rumi\Models;

class RunnableConfig extends AbstractRunnableConfig {

}
/**
 * @covers jakubsacha\Rumi\Models\AbstractRunnableConfig
 */
class AbstractRunnableConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testGivenNoCiContainerDefined_WhenGetCiContainerAsStringCalled_ThenFirstContainerIsUsed()
    {
        $oJob = new RunnableConfig(
            'name',
            ["www" => [], "second" => []],
            null,
            null,
            null
        );

        $this->assertEquals("www", $oJob->getCiContainer());
    }

    public function testGivenCiContainerIsDefined_WhenGetCiContainerAsStringCalled_ThenDefinedContainerIsUsed()
    {
        $oJob = new RunnableConfig(
            'name',
            ["www" => [], "second" => []],
            'second',
            null,
            null
        );

        $this->assertEquals("second", $oJob->getCiContainer());
    }

    public function testGivenParamsArePassed_WhenNewObjectCreated_ThenGettersAreFine()
    {
        $oJob = new RunnableConfig(
            'name',
            ["www" => [], "second" => []],
            'second',
            'third',
            ['fourth', 'sixth']
        );

        $this->assertEquals('name', $oJob->getName());
        $this->assertEquals('fourth ;sixth', $oJob->getCommandsAsString());
        $this->assertEquals(['fourth', 'sixth'], $oJob->getCommands());
        $this->assertEquals(["www" => [], "second" => []], $oJob->getDockerCompose());
        $this->assertEquals('third', $oJob->getEntryPoint());
    }

    public function testGivenEmptyCommands_WhenNewObjectCreated_ThenGetCommandAsStringReturnsNull()
    {
        $oJob = new RunnableConfig(
            'name',
            ["www" => [], "second" => []],
            'second',
            'third',
            null
        );

        $this->assertEquals('', $oJob->getCommandsAsString());
    }
}
