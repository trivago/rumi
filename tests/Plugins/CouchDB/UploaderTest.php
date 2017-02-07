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

namespace Trivago\Rumi\Plugins\CouchDB;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Trivago\Rumi\Plugins\CouchDB\Models\Run;

/**
 * @covers \Trivago\Rumi\Plugins\CouchDB\Uploader
 */
class UploaderTest extends TestCase
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
