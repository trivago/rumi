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

namespace Trivago\Rumi\Process;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class GitCheckoutProcessFactory
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $commitSha
     * @return Process
     */
    public function getCheckoutCommitProcess(InputInterface $input, OutputInterface $output, $commitSha)
    {
        $output->writeln('Checking out '.$input->getArgument('commit').' ');
        $process = new Process('git reset --hard && git checkout '.$commitSha);
        $process->setTimeout(600)->setIdleTimeout(600);

        return $process;
    }
}
