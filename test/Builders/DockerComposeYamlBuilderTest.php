<?php
namespace jakubsacha\Rumi\Builders;

use jakubsacha\Rumi\Docker\VolumeInspector;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @covers jakubsacha\Rumi\Builders\DockerComposeYamlBuilder
 */
class DockerComposeYamlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DockerComposeYamlBuilder
     */
    private $oSUT;

    /**
     * @var ObjectProphecy|VolumeInspector
     */
    private $oVolumeInspector;

    protected function setUp()
    {
        $this->oVolumeInspector = $this->prophesize(VolumeInspector::class);

        $this->oSUT = new DockerComposeYamlBuilder(
            $this->oVolumeInspector->reveal()
        );
    }

    public function testBuildReturnsPathToTmpYmlFile()
    {
        // given
        $oStageConfig = $this->prepareJobConfig();

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, ".");

        // then
        $this->assertFileExists($sYamlConfigFile);
        $this->assertContains("docker-compose.yml", $sYamlConfigFile);
    }

    public function testGivenEntryPointInConfig_WhenYamlFileBuild_ThenEntrypointIsSet()
    {
        // given
        $oStageConfig = $this->prepareJobConfig('test_entrypoint');

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, ".");
        $aYaml = $this->getYamlConfigFromFile($sYamlConfigFile);

        // then
        $this->assertEquals(
            'test_entrypoint',
            $aYaml['www']['entrypoint']
        );
    }

    public function testGivenEntrypointNullButCommandsSet_WhenYamlFileBuild_ThenEntrypointIsSetToDefaultShellAndCommandsAreSet()
    {
        // given
        $oStageConfig = $this->prepareJobConfig(null, ["echo 1"]);

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, ".");

        // then
        $aYaml = $this->getYamlConfigFromFile($sYamlConfigFile);
        $this->assertEquals(
            DockerComposeYamlBuilder::DEFAULT_SHELL,
            $aYaml['www']['entrypoint']
        );

        $this->assertEquals(['-c', 'echo 1'], $aYaml['www']['command']);
    }

    public function testGivenContainerHasPortsDefined_WhenYamlFileBuild_ThenPortsInformationIsDiscarded()
    {
        // given
        $oStageConfig = $this->prepareJobConfig(null, null, ["www"=>["ports"=>["80:80","443:443"]]]);

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, ".");

        // then
        $aYaml = $this->getYamlConfigFromFile($sYamlConfigFile);
        $this->assertArrayNotHasKey(
            "ports",
            $aYaml['www']
        );
    }

    public function testGivenContainerHasVolumesDefined_WhenYamlFileBuild_ThenVolumesAreAdjusted()
    {
        // given
        $oStageConfig = $this->prepareJobConfig(null, null, ["www"=>["volumes"=>[".:/var/www"]]]);

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, "__volume__");

        // then
        $aYaml = $this->getYamlConfigFromFile($sYamlConfigFile);
        $this->assertEquals(
            "__volume__:/var/www",
            $aYaml['www']['volumes'][0]
        );
    }

    public function testGivenVolumeMountingContainsPath_WhenYamlFileBuild_ThenVolumeIsAdjustedCorrectly()
    {
        // given
        $this->oVolumeInspector->getVolumeRealPath('__volume__')->willReturn('__volume_real_path__/');
        $oStageConfig = $this->prepareJobConfig(null, null, ["www"=>["volumes"=>["./some-file.abc:/var/www/some-file.abc"]]]);

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, "__volume__");

        // then
        $aYaml = $this->getYamlConfigFromFile($sYamlConfigFile);
        $this->assertEquals(
            "__volume_real_path__/some-file.abc:/var/www/some-file.abc",
            $aYaml['www']['volumes'][0]
        );
    }

    public function testGivenVolumeMountingContainsPathAndNoDockerVolumeInUse_WhenYamlFileBuild_ThenVolumeIsAdjustedCorrectly()
    {
        // given
        $oStageConfig = $this->prepareJobConfig(null, null, ["www"=>["volumes"=>["./some-file.abc:/var/www/some-file.abc"]]]);

        // when
        $sYamlConfigFile = $this->oSUT->build($oStageConfig, "/var/www/html/");

        // then
        $aYaml = $this->getYamlConfigFromFile($sYamlConfigFile);
        $this->assertEquals(
            "/var/www/html/some-file.abc:/var/www/some-file.abc",
            $aYaml['www']['volumes'][0]
        );
    }

    /**
     * @param string $sEntryPoint
     * @param array $aCommands
     * @return \jakubsacha\Rumi\Models\JobConfig
     */
    protected function prepareJobConfig($sEntryPoint = null, $aCommands = [], $aYamlConfig = [])
    {
        return new \jakubsacha\Rumi\Models\JobConfig(
            "job name",
            $aYamlConfig,
            "www",
            $sEntryPoint,
            $aCommands
        );
    }

    /**
     * @param $sOutput
     * @return mixed
     */
    protected function getYamlConfigFromFile($sOutput)
    {
        $oYamlReader = new \Symfony\Component\Yaml\Parser();
        $aYaml = $oYamlReader->parse(file_get_contents($sOutput));

        return $aYaml;
    }
}
