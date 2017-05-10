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

namespace Trivago\Rumi\Services\ConfigReaderFilterDecorator;

use PHPUnit\Framework\TestCase;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\StageConfig;
use Trivago\Rumi\Models\StagesCollection;
use Trivago\Rumi\Services\ConfigReaderInterface;

class EmptyStageFilterDecoratorTest extends TestCase
{
    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var FiltersInputParameters
     */
    private $parameters;

    /**
     * @var EmptyStageFilterDecorator
     */
    private $SUT;

    /**
     * @var StagesCollection
     */
    private $stagesCollection;

    protected function setUp()
    {
        $this->configReader = $this->prophesize(ConfigReaderInterface::class);
        $this->parameters = $this->prophesize(FiltersInputParameters::class);

        $this->SUT = new EmptyStageFilterDecorator(
            $this->configReader->reveal(),
            $this->parameters->reveal()
        );
    }

    public function testGivenFiltersAreDisabled_WhenExecuted_ThenNothingHappens()
    {
        // given
        $this->parameters->getStageFilter()->willReturn('')->shouldBeCalled();
        $this->parameters->getJobFilter()->willReturn('')->shouldBeCalled();

        $runConfig = $this->prophesize(RunConfig::class)->reveal();
        $this->configReader->getRunConfig()->willReturn($runConfig);

        // when
        $config = $this->SUT->getRunConfig();

        // then
        $this->assertSame($runConfig, $config);
    }

    public function testGivenOneFilterIsEnabled_WhenExecuted_ItFiltersOutEmptyStages()
    {
        // given
        $this->parameters->getStageFilter()->willReturn('should_be_kept')->shouldBeCalled();
        $this->parameters->getJobFilter()->willReturn('')->shouldBeCalled();

        /** @var RunConfig $runConfig */
        $runConfig = $this->prophesize(RunConfig::class);
        $this->configReader->getRunConfig()->willReturn($runConfig->reveal());

        $stagesCollection = $this->prophesize(StagesCollection::class);

        $jobConfigCollection = new JobConfigCollection();
        $jobConfigCollection->add(new JobConfig('', '', '', '', '', 0));
        $stageConfigToKeep = new StageConfig('should_be_kept', $jobConfigCollection);
        $stageConfigToRemove = new StageConfig('should_be_removed', new JobConfigCollection());

        $stagesCollection->getIterator()->willReturn(new \ArrayIterator([$stageConfigToKeep, $stageConfigToRemove]));

        $stagesCollection->remove($stageConfigToKeep)->shouldNotBeCalled();
        $stagesCollection->remove($stageConfigToRemove)->shouldBeCalled();

        $runConfig->getStagesCollection()->willReturn($stagesCollection->reveal());

        // when
        $config = $this->SUT->getRunConfig();

        // then
    }

}
