<?php
/**
 * @author jsacha
 * @since 01/03/16 22:35
 */

namespace jakubsacha\Rumi\Builders;


use jakubsacha\Rumi\Models\MetricConfig;

/**
 * @covers jakubsacha\Rumi\Builders\MetricConfigBuilder
 */
class MetricConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetricConfigBuilder
     */
    private $oSUT;

    /**
     * @var ComposeParser
     */
    private $compose_parser;

    protected function setUp()
    {
        $this->compose_parser = $this->prophesize(ComposeParser::class);

        $this->oSUT = new MetricConfigBuilder(
            $this->compose_parser->reveal()
        );
    }

    public function testGivenJobName_WhenProcessed_ThenJobNameIsAssigned()
    {
        //given

        //when
        $oSth = $this->oSUT->build(['jobName' => ['docker'=>['image'=>'php']]]);

        // then
        $this->assertContainsOnlyInstancesOf(MetricConfig::class, $oSth);
        $this->assertEquals('jobName', $oSth[0]->getName());
    }

}
