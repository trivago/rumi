<?php

namespace Trivago\Rumi\Plugins\CouchDB;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Trivago\Rumi\Plugins\CouchDB\Models\Run;
use Prophecy\Argument;

/**
 * @covers Trivago\Rumi\Plugins\CouchDB\Uploader
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

    /**
     * @var Response
     */
    private $revResponse;

    public function setUp()
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->response = $this->prophesize(Response::class);
        $this->revResponse = $this->prophesize(Response::class);

        $this->SUT = new Uploader('http://localhost/', $this->client->reveal());
    }

    public function testGivenFirstRun_WhenUploadIsTriggered_ThenRevIsRequested()
    {
        $run = new Run('commit_id');

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->revResponse->reveal())
            ->shouldBeCalled();
        $this->revResponse->getHeader('Etag')->willReturn(null);

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->response->reveal())
            ->shouldBeCalled();
        $this->response->getBody()->willReturn(json_encode(['rev' => '123']));

        // when
        $this->SUT->flush($run);
    }

    public function testGivenRun_WhenUploadIsTriggered_ThenItsPerformed()
    {
        // given
        $run = new Run('commit_id');

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->revResponse->reveal())
            ->shouldBeCalled();
        $this->revResponse->getHeader('Etag')->willReturn(null);

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->response->reveal())
            ->shouldBeCalled();
        $this->response->getBody()->willReturn(json_encode(['rev' => '123']));

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
            ->willReturn($this->revResponse->reveal())
            ->shouldBeCalled();
        $this->revResponse->getHeader('Etag')->willReturn(null);

        $this
            ->client
            ->send(Argument::type(Request::class))
            ->willReturn($this->response->reveal())
            ->shouldBeCalled();

        $this->response->getBody()->willReturn(json_encode(['rev' => '123']));

        // when
        $this->SUT->flush($run);
        $this->SUT->flush($run);

        // then
    }
}
