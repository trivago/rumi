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


use Symfony\Component\Console\Input\InputInterface;
use Trivago\Rumi\Commands\RunCommand;
use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Services\ConfigReaderInterface;

class EmptyStageFilterDecorator implements ConfigReaderInterface
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
     * @param ConfigReaderInterface $configReader
     * @param FiltersInputParameters $parameters
     */
    public function __construct(ConfigReaderInterface $configReader, FiltersInputParameters $parameters)
    {
        $this->configReader = $configReader;
        $this->parameters = $parameters;
    }

    /**
     * @return RunConfig
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function getRunConfig(): RunConfig
    {
        $config = $this->configReader->getRunConfig();

        $jobFilter = mb_strtolower($this->parameters->getJobFilter());
        $stageFilter = mb_strtolower($this->parameters->getStageFilter());

        // filter out empty stages, but only if any filter is enabled
        if (empty($jobFilter) && empty($stageFilter)) {
            return $config;
        }

        $stagesCollection = $config->getStagesCollection();
        foreach ($stagesCollection as $k => $stage) {
            $jobsCollection = $stage->getJobs();
            if ($jobsCollection->getIterator()->count() === 0) {
                $stagesCollection->remove($stage);
            }
        }

        return $config;
    }
}
