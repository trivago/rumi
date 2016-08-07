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

use Prophecy\Prophecy\ObjectProphecy;
use Trivago\Rumi\Docker\VolumeInspector;

/**
 * @covers Trivago\Rumi\Builders\DockerComposeYamlBuilder
 */
class DockerComposeYamlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DockerComposeYamlBuilder
     */
    private $SUT;

    /**
     * @var ObjectProphecy|VolumeInspector
     */
    private $volumeInspector;

    protected function setUp()
    {
        $this->volumeInspector = $this->prophesize(VolumeInspector::class);

        $this->SUT = new DockerComposeYamlBuilder(
            $this->volumeInspector->reveal()
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 3
     */
    public function testGivenMountFromRootFilesystemInConfig_WhenYamlFileBuild_ThenExceptionIsThrown()
    {
        // given
        $stageConfig = $this->prepareJobConfig(null, [], ['www' => ['volumes' => ['/:/sth']]]);

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '.');

        // then
        // exception is expected
    }

    public function testBuildReturnsPathToTmpYmlFile()
    {
        // given
        $stageConfig = $this->prepareJobConfig();

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '.');

        // then
        $this->assertFileExists($yamlConfigFile);
        $this->assertContains('docker-compose.yml', $yamlConfigFile);
    }

    public function testGivenEntryPointInConfig_WhenYamlFileBuild_ThenEntrypointIsSet()
    {
        // given
        $stageConfig = $this->prepareJobConfig('test_entrypoint');

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '.');
        $yaml = $this->getYamlConfigFromFile($yamlConfigFile);

        // then
        $this->assertEquals(
            'test_entrypoint',
            $yaml['www']['entrypoint']
        );
    }

    public function testGivenEntrypointNullButCommandsSet_WhenYamlFileBuild_ThenEntrypointIsSetToDefaultShellAndCommandsAreSet()
    {
        // given
        $stageConfig = $this->prepareJobConfig(null, ['echo 1']);

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '.');

        // then
        $yaml = $this->getYamlConfigFromFile($yamlConfigFile);
        $this->assertEquals(
            DockerComposeYamlBuilder::DEFAULT_SHELL,
            $yaml['www']['entrypoint']
        );

        $this->assertEquals(['-c', 'print "Executing command: echo 1" && echo 1'], $yaml['www']['command']);
    }

    public function testGivenContainerHasPortsDefined_WhenYamlFileBuild_ThenPortsInformationIsDiscarded()
    {
        // given
        $stageConfig = $this->prepareJobConfig(null, null, ['www' => ['ports' => ['80:80', '443:443']]]);

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '.');

        // then
        $yaml = $this->getYamlConfigFromFile($yamlConfigFile);
        $this->assertArrayNotHasKey(
            'ports',
            $yaml['www']
        );
    }

    public function testGivenContainerHasVolumesDefined_WhenYamlFileBuild_ThenVolumesAreAdjusted()
    {
        // given
        $stageConfig = $this->prepareJobConfig(null, null, ['www' => ['volumes' => ['.:/var/www']]]);

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '__volume__');

        // then
        $yaml = $this->getYamlConfigFromFile($yamlConfigFile);
        $this->assertEquals(
            '__volume__:/var/www',
            $yaml['www']['volumes'][0]
        );
    }

    public function testGivenVolumeMountingContainsPath_WhenYamlFileBuild_ThenVolumeIsAdjustedCorrectly()
    {
        // given
        $this->volumeInspector->getVolumeRealPath('__volume__')->willReturn('__volume_real_path__/');
        $stageConfig = $this->prepareJobConfig(null, null, ['www' => ['volumes' => ['./some-file.abc:/var/www/some-file.abc']]]);

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '__volume__');

        // then
        $yaml = $this->getYamlConfigFromFile($yamlConfigFile);
        $this->assertEquals(
            '__volume_real_path__/some-file.abc:/var/www/some-file.abc',
            $yaml['www']['volumes'][0]
        );
    }

    public function testGivenVolumeMountingContainsPathAndNoDockerVolumeInUse_WhenYamlFileBuild_ThenVolumeIsAdjustedCorrectly()
    {
        // given
        $stageConfig = $this->prepareJobConfig(null, null, ['www' => ['volumes' => ['./some-file.abc:/var/www/some-file.abc']]]);

        // when
        $yamlConfigFile = $this->SUT->build($stageConfig, '/var/www/html/');

        // then
        $yaml = $this->getYamlConfigFromFile($yamlConfigFile);
        $this->assertEquals(
            '/var/www/html/some-file.abc:/var/www/some-file.abc',
            $yaml['www']['volumes'][0]
        );
    }

    /**
     * @param string $entryPoint
     * @param array  $commands
     *
     * @return \Trivago\Rumi\Models\JobConfig
     */
    protected function prepareJobConfig($entryPoint = null, $commands = [], $yamlConfig = [])
    {
        return new \Trivago\Rumi\Models\JobConfig(
            'job name',
            $yamlConfig,
            'www',
            $entryPoint,
            $commands
        );
    }

    /**
     * @param $output
     *
     * @return mixed
     */
    protected function getYamlConfigFromFile($output)
    {
        $yamlReader = new \Symfony\Component\Yaml\Parser();
        $yaml = $yamlReader->parse(file_get_contents($output));

        return $yaml;
    }
}
