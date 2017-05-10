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

namespace Trivago\Rumi\Services\ConfigReaderFilterDecorator\Job;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Input\InputInterface;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\StageConfig;
use Trivago\Rumi\Models\StagesCollection;
use Trivago\Rumi\Services\ConfigReaderInterface;

/**
 * @covers \Trivago\Rumi\Services\ConfigReaderFilterDecorator\Job\JobFilterDecorator
 */
class JobFilterDecoratorTest extends TestCase
{
    /**
     * @var JobFilterDecorator
     */
    private $SUT;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var JobFilterParametersInterface|ObjectProphecy
     */
    private $parameters;

    protected function setUp()
    {
        $this->configReader = $this->prophesize(ConfigReaderInterface::class);
        $this->parameters = $this->prophesize(JobFilterParametersInterface::class);

        $this->SUT = new JobFilterDecorator(
            $this->configReader->reveal(),
            $this->parameters->reveal()
        );
    }

    public function testGivenNothingIsSet_WhenExecuted_NothingHappens()
    {
        // given
        $runConfig = $this->prophesize(RunConfig::class)->reveal();
        $this->parameters->getJobFilter()->willReturn('');

        $this
            ->configReader
            ->getRunConfig()
            ->willReturn($runConfig)
            ->shouldBeCalled();

        // when
        $config = $this->SUT->getRunConfig();

        // then
        $this->assertSame($runConfig, $config);
    }

    public function testGivenJobFilterIsSet_WhenExecuted_ItFiltersCorrectJobs()
    {
        // given
        $runConfig = $this->prophesize(RunConfig::class);
        $stageCollection = $this->prophesize(StagesCollection::class);
        $jobsConfigCollection = new JobConfigCollection();
        $jobsConfigCollection->add(new JobConfig('should_be_removed', '', '', '', '', 0));
        $jobsConfigCollection->add(new JobConfig('should_be_kept', '', '', '', '', 0));

        $stageConfig = new StageConfig('stage', $jobsConfigCollection);
        $stageConfig2 = new StageConfig('stage2', new JobConfigCollection());

        $stageCollection->getIterator()->willReturn(new \ArrayIterator([$stageConfig, $stageConfig2]));

        $runConfig->getStagesCollection()->willReturn(
            $stageCollection->reveal()
        );

        $this
            ->configReader
            ->getRunConfig()
            ->willReturn($runConfig->reveal())
            ->shouldBeCalled();

        // when
        $this->parameters->getJobFilter()->willReturn('should_be_kept')->shouldBeCalled();
        $this->SUT->getRunConfig();

        // then
        $this->assertEquals(1, $jobsConfigCollection->getIterator()->count());
    }

}
