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


use Trivago\Rumi\Models\RunConfig;

class ConfigReaderFilterDecorator implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $configReader;
    /**
     * @var null
     */
    private $stageFilter;
    /**
     * @var null
     */
    private $jobFilter;

    /**
     * ConfigReaderFilterDecorator constructor.
     * @param ConfigReaderInterface $configReader
     */
    public function __construct(
        ConfigReaderInterface $configReader
    )
    {
        $this->configReader = $configReader;
    }

    /**
     * @param $workingDir
     * @param $configFile
     *
     * @return RunConfig
     */
    public function getRunConfig($workingDir, $configFile)
    {
        $config = $this->configReader->getRunConfig($workingDir, $configFile);

        // filter out jobs that don't match filter
        if (!empty($this->jobFilter)) {
            $stagesCollection = $config->getStagesCollection();
            foreach ($stagesCollection as $k=>$stage){
                $jobsCollection = $stage->getJobs();
                foreach ($jobsCollection as $j=> $job){
                    if (strpos(mb_strtolower($job->getName()), $this->jobFilter) === false ){
                        $jobsCollection->remove($job);
                    }
                }
            }
        }

        // filter out stages that don't match filter
        if (!empty($this->stageFilter)) {
            $stagesCollection = $config->getStagesCollection();
            foreach ($stagesCollection->getIterator() as $k=>$stage){
                if (strpos(mb_strtolower($stage->getName()), $this->stageFilter) === false ){
                    $stagesCollection->remove($stage);
                }
            }
        }

        if (!empty($this->jobFilter) || !empty($this->stageFilter) ) {
            // filter out all empty stages
            $stagesCollection = $config->getStagesCollection();
            foreach ($stagesCollection->getIterator() as $k => $stage) {
                if (!count($stage->getJobs()->getIterator())) {
                    $stagesCollection->remove($stage);
                }
            }
        }

        return $config;
    }

    public function setStageFilter($stageFilter)
    {
        $this->stageFilter = mb_strtolower($stageFilter);
    }

    /**
     * @param null $jobFilter
     */
    public function setJobFilter($jobFilter)
    {
        $this->jobFilter = mb_strtolower($jobFilter);
    }

}
