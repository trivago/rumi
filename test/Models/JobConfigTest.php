<?php
/**
 * @author jsacha
 *
 * @since 02/03/16 12:14
 */

namespace jakubsacha\Rumi\Models;

/**
 * @covers jakubsacha\Rumi\Models\JobConfig
 */
class JobConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobConfig
     */
    private $SUT;

    public function setUp()
    {
        $this->SUT = new JobConfig(
            'name',
            ['www' => [], 'second' => []],
            'second',
            'third',
            ['fourth', 'sixth']
        );
    }

    public function testMetricsSetterAndGetter()
    {
        //given
        $metrics = [$this->prophesize(MetricConfig::class)->reveal()];

        $this->SUT->setMetrics($metrics);

        $this->assertEquals($metrics, $this->SUT->getMetrics());
    }
}
