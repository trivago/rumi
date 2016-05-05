<?php
/**
 * @author jsacha
 *
 * @since 19/02/16 23:08
 */

namespace jakubsacha\Rumi\Builders;

use jakubsacha\Rumi\Docker\VolumeInspector;
use jakubsacha\Rumi\Models\JobConfig;
use Symfony\Component\Yaml\Dumper;

class DockerComposeYamlBuilder
{
    const DEFAULT_SHELL = 'sh';
    /**
     * @var VolumeInspector
     */
    private $volumeInspector;

    public function __construct(VolumeInspector $volumeInspector)
    {
        $this->volumeInspector = $volumeInspector;
    }

    /**
     * Builds docker-compose.yml file based on JobConfig entity.
     *
     * @param JobConfig $stageConfig
     * @param string    $volume      docker volume or path to project files
     *
     * @return string
     */
    public function build(JobConfig $stageConfig, $volume)
    {
        $composeConfig = $stageConfig->getDockerCompose();

        $ciContainerName = $stageConfig->getCiContainer();
        $command = $stageConfig->getCommandsAsString();

        $entryPoint = $stageConfig->getEntryPoint();

        // if the entry point is specified, use it
        if (!empty($entryPoint)) {
            $composeConfig[$ciContainerName]['entrypoint'] = $entryPoint;
        }
        // otherwise we check do we have any commands specified, in case yes - we use default "sh" shell
        elseif (!empty($command)) {
            $composeConfig[$ciContainerName]['entrypoint'] = self::DEFAULT_SHELL;
        }

        // if we have any commands specified in yaml file, we put them into command part of compose file
        if (!empty($command)) {
            $composeConfig[$ciContainerName]['command'] =
                // escape $ to support docker syntax
                ['-c', str_replace('$', '$$', $command)];
        }

        // remove all port mappings (we do not want to expose anything) and fix volumes settings
        foreach ($composeConfig as $container => $settings) {
            unset($composeConfig[$container]['ports']);

            if (!isset($composeConfig[$container]['volumes'])) {
                continue;
            }

            foreach ($composeConfig[$container]['volumes'] as $volumeKey => $volumeSpec) {
                $composeConfig[$container]['volumes'][$volumeKey] = $this->handlePathsReplacement($volume, $volumeSpec);
            }
        }

        return $this->dumpFile($composeConfig);
    }

    /**
     * @param $parsedDockerCompose
     *
     * @return string file path
     */
    protected function dumpFile($parsedDockerCompose)
    {
        $tempTestDirectory = tempnam(sys_get_temp_dir(), 'RUNNER').md5(microtime()).'_d';
        usleep(1);
        mkdir($tempTestDirectory);

        $dumper = new Dumper();
        file_put_contents(
            $tempTestDirectory.'/docker-compose.yml',
            $dumper->dump($parsedDockerCompose)
        );

        return $tempTestDirectory.'/docker-compose.yml';
    }

    /**
     * @param $volume
     *
     * @return bool
     */
    protected function isDockerVolume($volume)
    {
        return $volume[0] != '/';
    }

    /**
     * @param $volumeName
     * @param $volumeSpecification
     *
     * @return mixed
     */
    public function handlePathsReplacement($volumeName, $volumeSpecification)
    {
        // if the full volume is mounted, there is no magic needed
        if (strpos($volumeSpecification, './') !== 0) {
            return str_replace('.:', $volumeName.':', $volumeSpecification);
        }

        // if we use systempath instead of docker volume
        if (!$this->isDockerVolume($volumeName)) {
            return str_replace('./', $volumeName, $volumeSpecification);
        }

        // we need to get real docker volume path and use it
        return str_replace('./', $this->volumeInspector->getVolumeRealPath($volumeName), $volumeSpecification);
    }
}
