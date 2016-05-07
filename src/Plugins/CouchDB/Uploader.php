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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Trivago\Rumi\Plugins\CouchDB\Models\Run;

class Uploader
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string|null
     */
    private $lastHash;

    /**
     * @var string|null
     */
    private $rev;

    /**
     * @var string
     */
    private $couchDBAddr;

    /**
     * Uploader constructor.
     */
    public function __construct($couchDBAddr, ClientInterface $client)
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->client = $client;
        $this->couchDBAddr = $couchDBAddr;
    }

    public function flush(Run $run)
    {
        if (empty($this->rev)) {
            $this->rev = $this->requestRev($run->getCommit());
        }
        $serializedRun = $this->serializer->serialize($run, JsonEncoder::FORMAT);

        // this part is here to avoid pushing the same output twice to CouchDB (saves bandwidth)
        $hash = md5($serializedRun);
        if ($this->lastHash == $hash) {
            // nothing to update
            return;
        }
        $this->lastHash = $hash;

        $request = new Request(
            'PUT',
            'http://' . $this->couchDBAddr . '/runs/' . $run->getCommit(),
            !empty($this->rev) ? ['If-Match' => $this->rev] : [],
            $serializedRun
        );

        $response = $this->client->send($request)->getBody();
        $json = json_decode($response);

        $this->rev = $json->rev;
    }

    private function requestRev($commitId)
    {
        try {
            $request = new Request(
                'HEAD',
                'http://' . $this->couchDBAddr . '/runs/' . $commitId
            );

            return trim(current($this->client->send($request)->getHeader('Etag')), '"');
        } catch (\Exception $e) {
            return;
        }
    }
}
