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

namespace Trivago\Rumi\Services;

use PHPUnit\Framework\TestCase;
use Trivago\Rumi\Models\JobConfig;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Models\StageConfig;
use Trivago\Rumi\Models\StagesCollection;

/**
 * @covers \Trivago\Rumi\Services\ConfigReaderFilterDecorator
 */
class ConfigReaderFilterDecoratorTest extends TestCase
{
    /**
     * @var ConfigReaderFilterDecorator
     */
    private $SUT;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    protected function setUp()
    {
        $this->configReader = $this->prophesize(ConfigReaderInterface::class);

        $this->SUT = new ConfigReaderFilterDecorator(
            $this->configReader->reveal()
        );
    }

    public function testGivenNothingIsSet_WhenExecuted_NothingHappens()
    {
        // given
        $runConfig = $this->prophesize(RunConfig::class)->reveal();

        $this
            ->configReader
            ->getRunConfig('', ConfigReader::CONFIG_FILE)
            ->willReturn($runConfig)
            ->shouldBeCalled();

        // when
        $config = $this->SUT->getRunConfig('', ConfigReader::CONFIG_FILE);

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

        $stageCollection->remove($stageConfig)->shouldNotBeCalled();
        $stageCollection->remove($stageConfig2)->shouldBeCalled();

        $runConfig->getStagesCollection()->willReturn(
            $stageCollection->reveal()
        );

        $this
            ->configReader
            ->getRunConfig('', ConfigReader::CONFIG_FILE)
            ->willReturn($runConfig->reveal())
            ->shouldBeCalled();

        // when
        $this->SUT->setJobFilter('should_be_kept');
        $this->SUT->getRunConfig('', ConfigReader::CONFIG_FILE);

        // then
    }

    public function testGivenStageFilterIsSet_WhenExecuted_ItFiltersCorrectJobs()
    {
        // given
        $runConfig = $this->prophesize(RunConfig::class);
        $stageCollection = $this->prophesize(StagesCollection::class);

        $jobConfigCollection = new JobConfigCollection();
        $jobConfigCollection->add(new JobConfig('', '', '', '', '', 0));
        $stageConfig = new StageConfig('should_be_kept', $jobConfigCollection);
        $stageConfig2 = new StageConfig('stage', new JobConfigCollection());

        $stageCollection->getIterator()->willReturn(new \ArrayIterator([$stageConfig, $stageConfig2]));

        $stageCollection->remove($stageConfig)->shouldNotBeCalled();
        $stageCollection->remove($stageConfig2)->shouldBeCalled();

        $runConfig->getStagesCollection()->willReturn(
            $stageCollection->reveal()
        );

        $this
            ->configReader
            ->getRunConfig('', ConfigReader::CONFIG_FILE)
            ->willReturn($runConfig->reveal())
            ->shouldBeCalled();

        // when
        $this->SUT->setStageFilter('should_be_kept');
        $this->SUT->getRunConfig('', ConfigReader::CONFIG_FILE);

        // then
    }
}
