<?php

namespace jakubsacha\Rumi\Plugins\CouchDB;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use jakubsacha\Rumi\Plugins\CouchDB\Models\Run;
use Prophecy\Argument;

/**
 * @covers jakubsacha\Rumi\Plugins\CouchDB\Uploader
 */
class UploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Uploader
     */
    private $SUT;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Response
     */
    private $response;

    public function setUp()
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->response = $this->prophesize(Response::class);

        $this->SUT = new Uploader("http://localhost/", $this->client->reveal());
    }

    public function testGivenRun_WhenUploadIsTriggered_ThenItsPerformed()
    {
        // given
        $run = new Run('commit_id');

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->response->reveal())
            ->shouldBeCalledTimes(1);

        $this->response->getBody()->willReturn(json_encode(['rev'=>'123']));

        // when
        $this->SUT->flush($run);

        // then
    }

    public function testGivenRun_WhenTriggeredTwice_UploadIsPerformedOnlyOnce()
    {
        // given
        $run = new Run('commit_id');

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->response->reveal())
            ->shouldBeCalledTimes(1);

        $this->response->getBody()->willReturn(json_encode(['rev'=>'123']));

        // when
        $this->SUT->flush($run);
        $this->SUT->flush($run);

        // then
    }
}
