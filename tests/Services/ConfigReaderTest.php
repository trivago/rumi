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

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Dumper;
use Trivago\Rumi\Builders\JobConfigBuilder;
use Trivago\Rumi\Commands\CommandAbstract;
use Trivago\Rumi\Models\JobConfigCollection;
use Trivago\Rumi\Models\StageConfig;
use Trivago\Rumi\Models\StagesCollection;

/**
 * @covers \Trivago\Rumi\Services\ConfigReader
 */
class ConfigReaderTest extends TestCase
{
    /**
     * @var ConfigReader
     */
    private $SUT;

    /**
     * @var JobConfigBuilder
     */
    private $jobConfigBuilder;

    public function setUp()
    {
        $this->jobConfigBuilder = $this->prophesize(JobConfigBuilder::class);

        $this->SUT = new ConfigReader($this->jobConfigBuilder->reveal());
        vfsStream::setup('directory');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 2
     * @expectedExceptionMessage Required file 'not_existing' does not exist
     */
    public function testGivenCiFileDoesNotExist_WhenGetConfigCalled_ThenExceptionIsThrown()
    {
        //given

        //when
        $this->SUT->getRunConfig(vfsStream::url('directory'), 'not_existing');

        //then
    }

    public function testGivenCiExist_WhenGetConfigCalled_ThenRunConfigIsReturned()
    {
        //given
        $cache = ['a', 'b', 'c'];
        $stages = ['stage1'=>[], 'stage2'=>[], 'stage3'=>[]];
        $merge_branch = 'origin/feature_1';

        $this->jobConfigBuilder->build([])->willReturn(new JobConfigCollection());

        $config = [
            'cache' => $cache,
            'stages' => $stages,
            'merge_branch' => $merge_branch,
        ];
        $dumper = new Dumper();

        file_put_contents(vfsStream::url('directory') . '/' . CommandAbstract::DEFAULT_CONFIG, $dumper->dump($config));

        //when
        $runConfig = $this->SUT->getRunConfig(vfsStream::url('directory') . '/', CommandAbstract::DEFAULT_CONFIG);

        //then
        $this->assertEquals($cache, iterator_to_array($runConfig->getCache()));
        $this->assertInstanceOf(StagesCollection::class, $runConfig->getStagesCollection());
        $this->assertEquals($merge_branch, $runConfig->getMergeBranch());
    }

    public function testGivenOnlyStageIsDefined_WhenGetConfigCalled_ThenRunConfigIsReturned()
    {
        //given
        $config = [
            'stages' => ['1'=>[], '2'=>[], '3'=>[]]
        ];
        file_put_contents(vfsStream::url('directory') . '/' . CommandAbstract::DEFAULT_CONFIG, (new Dumper())->dump($config));
        $this->jobConfigBuilder->build([])->willReturn(new JobConfigCollection());

        //when
        $runConfig = $this->SUT->getRunConfig(vfsStream::url('directory') . '/', CommandAbstract::DEFAULT_CONFIG);

        //then
        $this->assertEmpty(iterator_to_array($runConfig->getCache()));
        $this->assertInstanceOf(StagesCollection::class, $runConfig->getStagesCollection());
        $this->assertEmpty($runConfig->getMergeBranch());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to parse at line 2 (near "::yaml_file")
     */
    public function testGivenYamlFileSyntaxIsIncorrect_WhenExecuted_ThenItThrowsException()
    {
        //given
        file_put_contents(vfsStream::url('directory') . '/' . CommandAbstract::DEFAULT_CONFIG, 'wrong::' . PHP_EOL . '::yaml_file');

        // when
        $this->SUT->getRunConfig(vfsStream::url('directory') . '/', CommandAbstract::DEFAULT_CONFIG);

        // then
    }
}
