<?php
/**
 * @author jsacha
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
    private $oVolumeInspector;

    public function __construct(VolumeInspector $oVolumeInspector)
    {
        $this->oVolumeInspector = $oVolumeInspector;
    }

    /**
     * Builds docker-compose.yml file based on JobConfig entity
     *
     * @param JobConfig $oStageConfig
     * @param string $sVolume docker volume or path to project files
     * @return string
     */
    public function build(JobConfig $oStageConfig, $sVolume)
    {
        $_aPDC = $oStageConfig->getDockerCompose();

        $_sCiContainerName = $oStageConfig->getCiContainer();
        $_sCommand = $oStageConfig->getCommandsAsString();

        $_sEntryPoint = $oStageConfig->getEntryPoint();

        // if the entry point is specified, use it
        if (!empty($_sEntryPoint))
        {
            $_aPDC[$_sCiContainerName]['entrypoint'] = $_sEntryPoint;
        }
        // otherwise we check do we have any commands specified, in case yes - we use default "sh" shell
        else if (!empty($_sCommand))
        {
            $_aPDC[$_sCiContainerName]['entrypoint'] = self::DEFAULT_SHELL;
        }

        // if we have any commands specified in yaml file, we put them into command part of compose file
        if (!empty($_sCommand))
        {
            $_aPDC[$_sCiContainerName]['command'] =
                // escape $ to support docker syntax
                ['-c', str_replace('$', '$$', $_sCommand)];
        }

        // remove all port mappings (we do not want to expose anything) and fix volumes settings
        foreach ($_aPDC as $_sContainer => $_aSettings)
        {
            unset($_aPDC[$_sContainer]['ports']);

            if (!isset($_aPDC[$_sContainer]['volumes']))
            {
                continue;
            }

            foreach ($_aPDC[$_sContainer]['volumes'] as $_sVolumeKey => $_sVolumeSpec)
            {
                $_aPDC[$_sContainer]['volumes'][$_sVolumeKey] = $this->doSth($sVolume, $_sVolumeSpec);
            }
        }

        return $this->dumpFile($_aPDC);
    }

    /**
     * @param $_aParsedDockerCompose
     * @return string file path
     */
    protected function dumpFile($_aParsedDockerCompose)
    {
        $_sTempTestDirectory = tempnam(sys_get_temp_dir(), "RUNNER") . md5(microtime()).'_d';
        usleep(1);
        mkdir($_sTempTestDirectory);

        $oDumper = new Dumper();
        file_put_contents(
            $_sTempTestDirectory . '/docker-compose.yml',
            $oDumper->dump($_aParsedDockerCompose)
        );

        return $_sTempTestDirectory . '/docker-compose.yml';
    }

    /**
     * @param $sVolume
     * @return bool
     */
    protected function isDockerVolume($sVolume)
    {
        return $sVolume[0] != '/';
    }

    public function doSth($sVolume, $_sVolumeSpec)
    {
        // if the full volume is mounted, there is no magic needed
        if (strpos($_sVolumeSpec, "./") !== 0)
        {
            return str_replace('.:', $sVolume . ':', $_sVolumeSpec);
        }

        // if we use systempath instead of docker volume
        if (!$this->isDockerVolume($sVolume))
        {
            return str_replace('./', $sVolume, $_sVolumeSpec);
        }

        // we need to get real docker volume path and use it
        return str_replace('./', $this->oVolumeInspector->getVolumeRealPath($sVolume), $_sVolumeSpec);
    }

}