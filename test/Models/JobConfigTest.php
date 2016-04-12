<?php
/**
 * @author jsacha
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
    private $oSUT;

    public function setUp()
    {
        $this->oSUT = new JobConfig(
            'name',
            ["www" => [], "second" => []],
            'second',
            'third',
            ['fourth', 'sixth']
        );
    }

    public function testMetricsSetterAndGetter()
    {
        //given
        $aMetrics = [$this->prophesize(MetricConfig::class)->reveal()];

        $this->oSUT->setMetrics($aMetrics);

        $this->assertEquals($aMetrics, $this->oSUT->getMetrics());
    }
}
