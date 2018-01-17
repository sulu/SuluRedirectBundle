<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\DependencyInjection;

use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteNotUniqueException;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages redirect bundle configuration.
 */
class SuluRedirectExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('jms_serializer')) {
            $container->prependExtensionConfig(
                'jms_serializer',
                [
                    'metadata' => [
                        'directories' => [
                            'sulu_redirect' => [
                                'path' => __DIR__ . '/../Resources/config/serializer',
                                'namespace_prefix' => 'Sulu\Bundle\RedirectBundle\Entity',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('fos_rest')) {
            $container->prependExtensionConfig(
                'fos_rest',
                [
                    'exception' => [
                        'codes' => [
                            RedirectRouteNotUniqueException::class => 409,
                            EntityNotFoundException::class => 404,
                        ],
                    ],
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sulu_redirect.imports.path', $config['imports']['path']);
        $this->configurePersistence($config['objects'], $container);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('router.xml');
        $loader->load('import.xml');
        $loader->load('subscriber.xml');
    }
}
