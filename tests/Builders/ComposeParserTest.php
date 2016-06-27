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

use org\bovigo\vfs\vfsStream;

/**
 * @covers Trivago\Rumi\Builders\ComposeParser
 */
class ComposeParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposeParser
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new ComposeParser();
        vfsStream::setup('directory');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid docker configuration
     */
    public function testGivenInvalidDockerSpecified_WhenBuildExecuted_ThenExceptionIsThrown()
    {
        // given
        $composeConfig = 123;

        // when
        $this->SUT->parseComposePart($composeConfig);
    }

    public function testGivenDockerSpecifiedAsFilePath_WhenBuildExecuted_ThenJobConfigIsLoadedFromFile()
    {
        // given
        $composeFilePath = vfsStream::url('directory') . '/docker-compose.yml';
        file_put_contents($composeFilePath, 'www:' . PHP_EOL . '    image: php');

        // when
        $composeConfig = $this->SUT->parseComposePart($composeFilePath);

        // then
        $this->assertEquals('php', $composeConfig['www']['image']);
    }

    public function testGivenDockerSpecifiedAsArray_WhenBuildExecuted_ThenArrayConfigIsUsed()
    {
        // given
        $config['www']['image'] = 'php';

        // when
        $composeConfig = $this->SUT->parseComposePart($config);

        // then
        $this->assertEquals('php', $composeConfig['www']['image']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File docker-compose.yml does not exist
     */
    public function testGivenDockerSpecifiedAsFilePathAndFileDoesNotExist_WhenBuildExecuted_ThenExceptionIsThrown()
    {
        // given

        // when
        $this->SUT->parseComposePart('docker-compose.yml');
    }
}
