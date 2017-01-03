<?php
namespace Trivago\Rumi\Resources;

class WorkingDir
{
    /**
     * @var string
     */
    private $workingDir;

    /**
     * @param $dir
     */
    public function setWorkingDir($dir)
    {
        $this->workingDir = $dir;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getWorkingDir()
    {
        if (empty($this->workingDir)) {
            return;
        }

        return $this->workingDir.'/';
    }

}