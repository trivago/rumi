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


use Trivago\Rumi\Models\RunConfig;
use Trivago\Rumi\Services\ConfigReaderInterface;

class JobFilterDecorator implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $configReader;
    /**
     * @var JobFilterParametersInterface
     */
    private $parameters;

    /**
     * @param ConfigReaderInterface $configReader
     * @param JobFilterParametersInterface $parameters
     */
    public function __construct(ConfigReaderInterface $configReader, JobFilterParametersInterface $parameters)
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

        if (empty($this->parameters->getJobFilter())) {
            return $config;
        }

        $jobFilter = mb_strtolower($this->parameters->getJobFilter());

        // filter out jobs that don't match filter
        $stagesCollection = $config->getStagesCollection();
        foreach ($stagesCollection as $k=>$stage){
            $jobsCollection = $stage->getJobs();
            foreach ($jobsCollection as $j=> $job){
                if (strpos(mb_strtolower($job->getName()), $jobFilter) === false ){
                    $jobsCollection->remove($job);
                }
            }
        }

        return $config;
    }
}
