<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Application;

use Sulu\Bundle\RedirectBundle\SuluRedirectBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * AppKernel for functional tests.
 */
class Kernel extends SuluTestKernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return array_merge(parent::registerBundles(), [new SuluRedirectBundle()]);
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/config/config.yml');
    }

    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();

        $reflection = new \ReflectionClass(\Gedmo\Exception::class);
        $gedmoDirectory = \dirname($reflection->getFileName());

        $parameters['gedmo_directory'] = $gedmoDirectory;

        return $parameters;
    }
}
