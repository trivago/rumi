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

namespace Trivago\Rumi\Services\ConfigReaderFilterDecorator\Stage;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\StageConfig;
use Trivago\Rumi\Models\StagesCollection;
use Trivago\Rumi\Services\ConfigReaderInterface;

/**
 * @covers \Trivago\Rumi\Services\ConfigReaderFilterDecorator\Stage\StageFilterDecorator
 */
class StageFilterDecoratorTest extends TestCase
{
    /**
     * @var StageFilterDecorator
     */
    private $SUT;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var StageFilterParametersInterface|ObjectProphecy
     */
    private $parameters;

    protected function setUp()
    {
        $this->configReader = $this->prophesize(ConfigReaderInterface::class);
        $this->parameters = $this->prophesize(StageFilterParametersInterface::class);

        $this->SUT = new StageFilterDecorator(
            $this->configReader->reveal(),
            $this->parameters->reveal()
        );
    }

    public function testGivenNothingIsSet_WhenExecuted_NothingHappens()
    {
        // given
        $runConfig = $this->prophesize(RunConfig::class)->reveal();
        $this->parameters->getStageFilter()->willReturn('');

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


    public function testGivenStageFilterIsSet_WhenExecuted_ItFiltersCorrectJobs()
    {
        // given
        $this->parameters->getStageFilter()->willReturn('should_be_kept')->shouldBeCalled();

        $runConfig = $this->prophesize(RunConfig::class);
        $stageCollection = $this->prophesize(StagesCollection::class);

        $jobConfigCollection = new JobConfigCollection();
        $jobConfigCollection->add(new JobConfig('', '', '', '', '', 0));
        $stageConfig = new StageConfig('should_be_kept', $jobConfigCollection);
        $stageConfig2 = new StageConfig('should_be_removed', new JobConfigCollection());

        $stageCollection->getIterator()->willReturn(new \ArrayIterator([$stageConfig, $stageConfig2]));

        $stageCollection->remove($stageConfig)->shouldNotBeCalled();
        $stageCollection->remove($stageConfig2)->shouldBeCalled();

        $runConfig->getStagesCollection()->willReturn(
            $stageCollection->reveal()
        );

        $this
            ->configReader
            ->getRunConfig()
            ->willReturn($runConfig->reveal())
            ->shouldBeCalled();

        // when
        $this->SUT->getRunConfig();

        // then
    }
}
