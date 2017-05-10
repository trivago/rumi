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
use Trivago\Rumi\Services\ConfigReaderFilterDecorator\Job\JobFilterParametersInterface;
use Trivago\Rumi\Services\ConfigReaderFilterDecorator\Stage\StageFilterParametersInterface;

class FiltersInputParameters implements JobFilterParametersInterface, StageFilterParametersInterface
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param InputInterface $input
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public function getJobFilter(): string
    {
        if (!$this->input->hasOption(RunCommand::JOB_FILTER)) {
            return '';
        }
        return (string)$this->input->getOption(RunCommand::JOB_FILTER);
    }

    public function getStageFilter(): string
    {
        if (!$this->input->hasOption(RunCommand::STAGE_FILTER)) {
            return '';
        }
        return (string)$this->input->getOption(RunCommand::STAGE_FILTER);
    }
}
