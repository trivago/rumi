<?php

namespace jakubsacha\Rumi\Builders;

use Symfony\Component\Yaml\Parser;

class ComposeParser
{
    /**
     * @param $sDockerFilePath
     * @return array|mixed|string
     * @throws \Exception
     */
    public function parseComposePart($sDockerFilePath)
    {
        if (is_string($sDockerFilePath))
        {
            return $this->loadDockerCompose($sDockerFilePath);
        }

        if (is_array($sDockerFilePath))
        {
            return $sDockerFilePath;
        }

        throw new \Exception(sprintf('Invalid docker configuration %s', $sDockerFilePath));
    }

    /**
     * @param $sDockerFilePath
     * @return mixed
     * @throws \Exception
     */
    private function loadDockerCompose($sDockerFilePath)
    {
        if (!file_exists($sDockerFilePath))
        {
            throw new \Exception(sprintf("File %s does not exist", $sDockerFilePath));
        }
        $oParser = new Parser();
        $aParsed = $oParser->parse(file_get_contents($sDockerFilePath));

        return $aParsed;
    }
}