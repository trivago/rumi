<?php
/**
 * @author jsacha
 * @since 06/05/16 09:33
 */

namespace jakubsacha\Rumi\Plugins\CouchDB;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use jakubsacha\Rumi\Plugins\CouchDB\Models\Run;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

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
            ['If-Match' => $this->rev],
            $serializedRun
        );

        $response = $this->client->send($request)->getBody();
        $json = json_decode($response);

        $this->rev = $json->rev;
    }
}