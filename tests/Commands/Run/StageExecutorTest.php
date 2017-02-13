<?php
/**
 * @author jsacha
 * @since 26/10/2016 19:02
 */

namespace Trivago\Rumi\Commands\Run;


use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trivago\Rumi\Builders\DockerComposeYamlBuilder;
use Trivago\Rumi\Models\VCSInfo\VCSInfoInterface;
use Trivago\Rumi\Process\RunningProcessFactoryInterface;

class StageExecutorTest extends TestCase
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
     * @var RunningProcessFactoryInterface|ObjectProphecy
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
        $this->runningProcessFactory = $this->prophesize(RunningProcessFactoryInterface::class);
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
