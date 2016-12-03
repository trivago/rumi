<?php
/**
 * @author jsacha
 * @since 26/10/2016 19:02
 */

namespace Trivago\Rumi\Commands\Run;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trivago\Rumi\Builders\DockerComposeYamlBuilder;
use Trivago\Rumi\Models\VCSInfo\VCSInfoInterface;
use Trivago\Rumi\Process\RunningProcessesFactory;

class StageExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var DockerComposeYamlBuilder|ObjectProphecy
     */
    private $dockerComposeYamlBuilder;

    /**
     * @var RunningProcessesFactory|ObjectProphecy
     */
    private $runningProcessFactory;

    /**
     * @var StageExecutor
     */
    private $SUT;

    /**
     * @var VCSInfoInterface|ObjectProphecy
     */
    private $VCSInfo;

    /**
     * @var OutputInterface
     */
    private $outputInterface;


    protected function setUp()
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->dockerComposeYamlBuilder = $this->prophesize(DockerComposeYamlBuilder::class);
        $this->runningProcessFactory = $this->prophesize(RunningProcessesFactory::class);
        $this->VCSInfo = $this->prophesize(VCSInfoInterface::class);

        $this->outputInterface = new BufferedOutput();

        $this->SUT = new StageExecutor(
            $this->eventDispatcher->reveal(),
            $this->dockerComposeYamlBuilder->reveal(),
            $this->runningProcessFactory->reveal()
        );
    }


    public function testABC()
    {
        $this->markTestSkipped("Needs to be implemented");
        // given

        // when
        $this->SUT->executeStage(
            [],
            '',
            $this->outputInterface,
            $this->VCSInfo->reveal()
        );

        // then


    }
}
