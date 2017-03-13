<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Admin;

use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationItem;
use Sulu\Bundle\AdminBundle\Navigation\ContentNavigationProviderInterface;

/**
 * Provides tabs for redirect-routes.
 */
class RedirectRoutesNavigationProvider implements ContentNavigationProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNavigationItems(array $options = [])
    {
        $details = new ContentNavigationItem('sulu_redirect.details');
        $details->setAction('details');
        $details->setPosition(10);
        $details->setComponent('redirects/edit/details@suluredirect');

        return [$details];
    }
}
