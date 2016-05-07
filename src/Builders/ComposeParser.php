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

use Symfony\Component\Yaml\Parser;

class ComposeParser
{
    /**
     * @param $dockerFilePath
     *
     * @throws \Exception
     *
     * @return array|mixed|string
     */
    public function parseComposePart($dockerFilePath)
    {
        if (is_string($dockerFilePath)) {
            return $this->loadDockerCompose($dockerFilePath);
        }

        if (is_array($dockerFilePath)) {
            return $dockerFilePath;
        }

        throw new \Exception(sprintf('Invalid docker configuration %s', $dockerFilePath));
    }

    /**
     * @param $dockerFilePath
     *
     * @throws \Exception
     *
     * @return mixed
     */
    private function loadDockerCompose($dockerFilePath)
    {
        if (!file_exists($dockerFilePath)) {
            throw new \Exception(sprintf('File %s does not exist', $dockerFilePath));
        }
        $parser = new Parser();

        return $parser->parse(file_get_contents($dockerFilePath));
    }
}
