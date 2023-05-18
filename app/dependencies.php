<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        EntityManager::class => function (ContainerInterface $container): EntityManager {
            $settings = $container->get(SettingsInterface::class)->get('doctrine');

            $config = ORMSetup::createAttributeMetadataConfiguration(
                $settings['metadata_dirs'],
                $settings['dev_mode'],
                $settings['proxy_dir']
            );

            $cache = new FilesystemAdapter('', 0, $settings['cache_dir']);
            $config->setMetadataCache($cache);

            $connection = DriverManager::getConnection($settings['connection']);
        
            return new EntityManager(
                $connection,
                $config
            );
        }
    ]);
};
