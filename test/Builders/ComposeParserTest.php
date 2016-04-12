<?php
/**
 * @author jsacha
 * @since 02/03/16 11:22
 */

namespace jakubsacha\Rumi\Builders;

use org\bovigo\vfs\vfsStream;

/**
 * @covers jakubsacha\Rumi\Builders\ComposeParser
 */
class ComposeParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposeParser
     */
    private $oSUT;

    protected function setUp()
    {
        $this->oSUT = new ComposeParser();
        vfsStream::setup('directory');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid docker configuration
     */
    public function testGivenInvalidDockerSpecified_WhenBuildExecuted_ThenExceptionIsThrown()
    {
        // given
        $compose_config = 123;

        // when
        $this->oSUT->parseComposePart($compose_config);

    }


    public function testGivenDockerSpecifiedAsFilePath_WhenBuildExecuted_ThenJobConfigIsLoadedFromFile()
    {
        // given
        $_sComposeFilePath = vfsStream::url('directory') . "/docker-compose.yml";
        file_put_contents($_sComposeFilePath, "www:".PHP_EOL."    image: php");

        // when
        $aComposeConfig = $this->oSUT->parseComposePart($_sComposeFilePath);

        // then
        $this->assertEquals("php", $aComposeConfig['www']['image']);
    }



    public function testGivenDockerSpecifiedAsArray_WhenBuildExecuted_ThenArrayConfigIsUsed()
    {
        // given
        $aConfig['www']['image'] = 'php';

        // when
        $aComposeConfig = $this->oSUT->parseComposePart($aConfig);

        // then
        $this->assertEquals("php", $aComposeConfig['www']['image']);
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File docker-compose.yml does not exist
     */
    public function testGivenDockerSpecifiedAsFilePathAndFileDoesNotExist_WhenBuildExecuted_ThenExceptionIsThrown()
    {
        // given

        // when
        $this->oSUT->parseComposePart('docker-compose.yml');
    }
}
