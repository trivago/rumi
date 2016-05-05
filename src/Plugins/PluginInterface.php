<?php
/**
 * @author jsacha
 * @since 29/04/16 15:25
 */

namespace jakubsacha\Rumi\Plugins;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface PluginInterface
{
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    );
}