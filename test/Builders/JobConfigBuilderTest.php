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

namespace Trivago\Rumi\Builders;

use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\MetricConfig;

/**
 * @covers Trivago\Rumi\Builders\JobConfigBuilder
 */
class JobConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobConfigBuilder
     */
    private $SUT;

    /**
     * @var MetricConfigBuilder
     */
    private $metricsBuilder;

    /**
     * @var ComposeParser
     */
    private $composeParser;

    protected function setUp()
    {
        $this->metricsBuilder = $this->prophesize(MetricConfigBuilder::class);
        $this->composeParser = $this->prophesize(ComposeParser::class);

        $this->SUT = new JobConfigBuilder(
            $this->metricsBuilder->reveal(),
            $this->composeParser->reveal()
        );
    }

    public function testGivenEmptyJobsGiven_WhenBuildExecuted_ThenOutputIsEmptyArray()
    {
        // given
        $config = [];

        // when
        $jobs = $this->SUT->build($config);

        // then
        $this->assertEmpty($jobs);
        $this->assertTrue(is_array($jobs));
    }

    public function testGivenOneJobDefined_WhenBuildExecuted_ThenOutputIsCorrectJobObject()
    {
        // given
        $config = [
            'Do something fun' => [
            ],
        ];

        // when
        $jobs = $this->SUT->build($config);

        // then
        $this->assertCount(1, $jobs);
        /** @var JobConfig $job */
        $job = $jobs[0];

        $this->assertInstanceOf(JobConfig::class, $job);
        $this->assertSame('Do something fun', $job->getName());
    }

    public function testGivenJobWithCiImageSpecified_WhenBuildExecuted_ThenJobConfigContainsCiImage()
    {
        // given
        $config = [
            'Do something fun' => [
                'ci_image' => '__container__',
            ],
        ];

        // when
        $jobs = $this->SUT->build($config);

        // then
        $this->assertCount(1, $jobs);
        /** @var JobConfig $job */
        $job = $jobs[0];

        $this->assertSame('__container__', $job->getCiContainer());
    }

    public function testGivenJobWithEntypointSpecified_WhenBuildExecuted_ThenJobConfigContainsEntrypoint()
    {
        // given
        $config = [
            'Do something fun' => [
                'entrypoint' => '__entrypoint__',
            ],
        ];

        // when
        $jobs = $this->SUT->build($config);

        // then
        $this->assertCount(1, $jobs);
        /** @var JobConfig $job */
        $job = $jobs[0];

        $this->assertSame('__entrypoint__', $job->getEntryPoint());
    }

    public function testGivenJobWithCommandSpecified_WhenBuildExecuted_ThenJobConfigContainsCommand()
    {
        // given
        $commands = ['__commands__'];
        $config = [
            'Do something fun' => [
                'commands' => $commands,
            ],
        ];

        // when
        $jobs = $this->SUT->build($config);

        // then
        $this->assertCount(1, $jobs);
        /** @var JobConfig $job */
        $job = $jobs[0];

        $this->assertSame($commands, $job->getCommands());
    }

    public function testGivenComposeFileContainsMetrics_WhenBuildExecuted_ThenMetricsAreBuild()
    {
        // given
        $config = [
            'Job one' => [
                'docker' => [
                    'image' => 'php:latest',
                ],
                'metrics' => [
                    'Some fun metric' => [

                    ],
                ],
            ],
        ];

        $metricConfig = [new MetricConfig('name', 'docker_compose', 'ci_container', 'entrypoint', 'commands')];

        $this->metricsBuilder
            ->build(['Some fun metric' => [

                ]])
            ->willReturn($metricConfig)
            ->shouldBeCalled();

        // when
        $jobConfigs = $this->SUT->build($config);

        // then
        $this->assertContainsOnlyInstancesOf($metricConfig[0], $jobConfigs[0]->getMetrics());
    }

    public function testGivenEmptyJobsDefinition_WhenBuilderExecuted_ThenItDoesNothing()
    {
        // given

        // when
        $jobs = $this->SUT->build(null);

        // then
        $this->assertEmpty($jobs);
    }
}
