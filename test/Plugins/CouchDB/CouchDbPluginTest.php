<?php
/**
 * @author jsacha
 *
 * @since 06/05/16 09:59
 */

namespace Trivago\Rumi\Plugins\CouchDB;

use Trivago\Rumi\Events;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers Trivago\Rumi\Plugins\CouchDB\CouchDbPlugin
 */
class CouchDbPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenNoEnvVariablePassed_WhenCreated_ThenNothingHappens()
    {
        // given
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        // when
        new CouchDbPlugin(
            $this->prophesize(InputInterface::class)->reveal(),
            $this->prophesize(OutputInterface::class)->reveal(),
            $this->prophesize(Application::class)->reveal(),
            $this->prophesize(ContainerInterface::class)->reveal(),
            $eventDispatcher->reveal()
        );

        // then
        $eventDispatcher->addListener(Argument::any(), Argument::any())->shouldNotBeCalled();
    }

    public function testGivenNoEnvVariablePassed_WhenCreated_ThenItSetsUpEventListeners()
    {
        // given
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        putenv(CouchDbPlugin::ENV_VARIABLE.'=abc');

        // when
        new CouchDbPlugin(
            $this->prophesize(InputInterface::class)->reveal(),
            $this->prophesize(OutputInterface::class)->reveal(),
            $this->prophesize(Application::class)->reveal(),
            $this->prophesize(ContainerInterface::class)->reveal(),
            $eventDispatcher->reveal()
        );

        // then
        $eventDispatcher->addListener(Events::RUN_STARTED, Argument::any())->shouldBeCalledTimes(1);
    }
}
