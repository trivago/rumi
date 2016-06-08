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

use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trivago\Rumi\Events;

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
