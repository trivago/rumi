<?php
/**
 * @author jsacha
 *
 * @since 02/03/16 11:22
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
        $composeFilePath = vfsStream::url('directory').'/docker-compose.yml';
        file_put_contents($composeFilePath, 'www:'.PHP_EOL.'    image: php');

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
