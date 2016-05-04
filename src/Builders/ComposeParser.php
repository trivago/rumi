<?php

namespace Trivago\Rumi\Builders;

use Symfony\Component\Yaml\Parser;

class ComposeParser
{
    /**
     * @param $dockerFilePath
     *
     * @return array|mixed|string
     *
     * @throws \Exception
     */
    public function parseComposePart($dockerFilePath)
    {
        if (is_string($dockerFilePath)) {
            return $this->loadDockerCompose($dockerFilePath);
        }

        if (is_array($dockerFilePath)) {
            return $dockerFilePath;
        }

        throw new \Exception(sprintf('Invalid docker configuration %s', $dockerFilePath));
    }

    /**
     * @param $dockerFilePath
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function loadDockerCompose($dockerFilePath)
    {
        if (!file_exists($dockerFilePath)) {
            throw new \Exception(sprintf('File %s does not exist', $dockerFilePath));
        }
        $parser = new Parser();

        return $parser->parse(file_get_contents($dockerFilePath));
    }
}
