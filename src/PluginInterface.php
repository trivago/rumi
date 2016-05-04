<?php
/**
 * @author jsacha
 * @since 29/04/16 15:25
 */

namespace jakubsacha\Rumi;


use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface PluginInterface
{
    public function initialize(
        Application $application,
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher
    );
}