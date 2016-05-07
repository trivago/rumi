<?php
/**
 * @author jsacha
 *
 * @since 01/03/16 22:35
 */

namespace Trivago\Rumi\Builders;

use Trivago\Rumi\Models\MetricConfig;

/**
 * @covers Trivago\Rumi\Builders\MetricConfigBuilder
 */
class MetricConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetricConfigBuilder
     */
    private $SUT;

    /**
     * @var ComposeParser
     */
    private $composeParser;

    protected function setUp()
    {
        $this->composeParser = $this->prophesize(ComposeParser::class);

        $this->SUT = new MetricConfigBuilder(
            $this->composeParser->reveal()
        );
    }

    public function testGivenJobName_WhenProcessed_ThenJobNameIsAssigned()
    {
        //given

        //when
        $metricConfig = $this->SUT->build(['jobName' => ['docker' => ['image' => 'php']]]);

        // then
        $this->assertContainsOnlyInstancesOf(MetricConfig::class, $metricConfig);
        $this->assertEquals('jobName', $metricConfig[0]->getName());
    }
}
