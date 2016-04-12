<?php
namespace jakubsacha\Rumi\Builders;

use jakubsacha\Rumi\Models\JobConfig;
use jakubsacha\Rumi\Models\MetricConfig;

/**
 * @covers jakubsacha\Rumi\Builders\JobConfigBuilder
 */
class JobConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobConfigBuilder
     */
    private $oSUT;

    /**
     * @var MetricConfigBuilder
     */
    private $metrics_builder;

    /**
     * @var ComposeParser
     */
    private $compose_parser;

    protected function setUp()
    {
        $this->metrics_builder = $this->prophesize(MetricConfigBuilder::class);
        $this->compose_parser = $this->prophesize(ComposeParser::class);

        $this->oSUT = new JobConfigBuilder(
            $this->metrics_builder->reveal(),
            $this->compose_parser->reveal()
        );
    }

    public function testGivenEmptyJobsGiven_WhenBuildExecuted_ThenOutputIsEmptyArray()
    {
        // given
        $aConfig = [];

        // when
        $aJobs = $this->oSUT->build($aConfig);

        // then
        $this->assertEmpty($aJobs);
        $this->assertTrue(is_array($aJobs));
    }

    public function testGivenOneJobDefined_WhenBuildExecuted_ThenOutputIsCorrectJobObject()
    {
        // given
        $aConfig = [
            'Do something fun' => [
            ]
        ];

        // when
        $aJobs = $this->oSUT->build($aConfig);

        // then
        $this->assertCount(1, $aJobs);
        /** @var JobConfig $oJob */
        $oJob = $aJobs[0];

        $this->assertInstanceOf(JobConfig::class, $oJob);
        $this->assertSame("Do something fun", $oJob->getName());
    }

    public function testGivenJobWithCiImageSpecified_WhenBuildExecuted_ThenJobConfigContainsCiImage()
    {
        // given
        $aConfig = [
            'Do something fun' => [
                'ci_image'=>'__container__',
            ]
        ];

        // when
        $aJobs = $this->oSUT->build($aConfig);

        // then
        $this->assertCount(1, $aJobs);
        /** @var JobConfig $oJob */
        $oJob = $aJobs[0];

        $this->assertSame("__container__", $oJob->getCiContainer());
    }

    public function testGivenJobWithEntypointSpecified_WhenBuildExecuted_ThenJobConfigContainsEntrypoint()
    {
        // given
        $aConfig = [
            'Do something fun' => [
                'entrypoint'=>'__entrypoint__',
            ]
        ];

        // when
        $aJobs = $this->oSUT->build($aConfig);

        // then
        $this->assertCount(1, $aJobs);
        /** @var JobConfig $oJob */
        $oJob = $aJobs[0];

        $this->assertSame("__entrypoint__", $oJob->getEntryPoint());
    }

    public function testGivenJobWithCommandSpecified_WhenBuildExecuted_ThenJobConfigContainsCommand()
    {
        // given
        $aCommands = ['__commands__'];
        $aConfig = [
            'Do something fun' => [
                'commands'=> $aCommands,
            ]
        ];

        // when
        $aJobs = $this->oSUT->build($aConfig);

        // then
        $this->assertCount(1, $aJobs);
        /** @var JobConfig $oJob */
        $oJob = $aJobs[0];

        $this->assertSame($aCommands, $oJob->getCommands());
    }

    public function testGivenComposeFileContainsMetrics_WhenBuildExecuted_ThenMetricsAreBuild()
    {
        // given
        $aConfig = [
            'Job one' =>
            [
                'docker'=> [
                    'image' => 'php:latest'
                ],
                'metrics' =>[
                    'Some fun metric' =>
                    [

                    ]
                ]
            ]
        ];

        $metricConfig = [new MetricConfig('name', 'docker_compose', 'ci_container', 'entrypoint', 'commands')];

        $this->metrics_builder
            ->build(['Some fun metric' =>
                [

                ]])
            ->willReturn($metricConfig)
            ->shouldBeCalled();

        // when
        $aJobConfigs = $this->oSUT->build($aConfig);

        // then
        $this->assertContainsOnlyInstancesOf($metricConfig[0], $aJobConfigs[0]->getMetrics());
    }

}
