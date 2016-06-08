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
