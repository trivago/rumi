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

namespace Trivago\Rumi;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Trivago\Rumi\Commands\CacheRestoreCommand;
use Trivago\Rumi\Commands\CacheStoreCommand;
use Trivago\Rumi\Commands\CheckoutCommand;
use Trivago\Rumi\Commands\RunCommand;
use Trivago\Rumi\Plugins\CouchDB\CouchDbPlugin;

class RumiApplication extends Application
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * RumiApplication constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initContainer();
        $this->setUpCommands();
    }

    /**
     * @return ContainerBuilder
     */
    private function initContainer()
    {
        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load('Resources/config/services.xml');
    }

    public function loadPlugins(InputInterface $input, OutputInterface $output)
    {
        new CouchDbPlugin($input, $output, $this, $this->container, $this->container->get('trivago.rumi.event_dispatcher'));
    }

    private function setUpCommands()
    {
        $oRunCommand = new RunCommand(
            $this->container->get('trivago.rumi.event_dispatcher'),
            $this->container->get('trivago.rumi.services.config_reader'),
            $this->container->get('trivago.rumi.commands.run.stage_executor')
        );
        $this->add($oRunCommand);
        $this->add(new CheckoutCommand($this->container, $this->container->get('trivago.rumi.validators.git_checkout_validator')));
        $this->add(new CacheStoreCommand($this->container));
        $this->add(new CacheRestoreCommand($this->container));
        $this->setDefaultCommand($oRunCommand->getName());
    }
}
