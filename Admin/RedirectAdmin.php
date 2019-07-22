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

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Routing\RouteBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Navigation\Navigation;
use Sulu\Bundle\AdminBundle\Navigation\NavigationItem;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Integrates redirect-bundle into sulu-admin.
 */
class RedirectAdmin extends Admin
{
    const SECURITY_CONTEXT = 'sulu.modules.redirects';

    const LIST_ROUTE = 'sulu_redirect.list';

    const ADD_FORM_ROUTE = 'sulu_redirect.add_form';

    const EDIT_FORM_ROUTE = 'sulu_redirect.edit_form';

    /**
     * @var RouteBuilderFactoryInterface
     */
    private $routeBuilderFactory;

    /**
     * @var SecurityCheckerInterface
     */
    protected $securityChecker;

    /**
     * RedirectAdmin constructor.
     * @param RouteBuilderFactoryInterface $routeBuilderFactory
     * @param SecurityCheckerInterface $securityChecker
     */
    public function __construct(RouteBuilderFactoryInterface $routeBuilderFactory, SecurityCheckerInterface $securityChecker)
    {
        $this->routeBuilderFactory = $routeBuilderFactory;
        $this->securityChecker = $securityChecker;
    }


    public function getNavigation(): Navigation
    {
        $rootNavigationItem = $this->getNavigationItemRoot();

        $settings = Admin::getNavigationItemSettings();

        if ($this->securityChecker->hasPermission(self::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $redirect = new NavigationItem('sulu_redirect.title', $settings);
            $redirect->setPosition(51);
            $redirect->setMainRoute(static::LIST_ROUTE);
        }

        if ($settings->hasChildren()) {
            $rootNavigationItem->addChild($settings);
        }

        return new Navigation($rootNavigationItem);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'suluredirect';
    }

    public function getRoutes(): array
    {
        $formToolbarActions = [
            'sulu_admin.save',
            'sulu_admin.delete',
        ];

        $listToolbarActions = [
            'sulu_admin.add',
            'sulu_admin.delete'
        ];

        return [
            $this->routeBuilderFactory->createListRouteBuilder(static::LIST_ROUTE, '/redirect-routes')
                ->setResourceKey('redirect_routes')
                ->setListKey('redirect_routes')
                ->setTitle('sulu_redirect.title')
                ->addListAdapters(['table'])
                ->setAddRoute(static::ADD_FORM_ROUTE)
                ->setEditRoute(static::EDIT_FORM_ROUTE)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
                ->getRoute(),
            $this->routeBuilderFactory->createResourceTabRouteBuilder(static::ADD_FORM_ROUTE, '/redirect-routes/add')
                ->setResourceKey('redirect_routes')
                ->setBackRoute(static::LIST_ROUTE)
                ->getRoute(),
            $this->routeBuilderFactory->createFormRouteBuilder('sulu_redirects.add_form.details', '/details')
                ->setResourceKey('redirect_routes')
                ->setFormKey('redirect_route_details')
                ->setTabTitle('sulu_admin.details')
                ->setEditRoute(static::EDIT_FORM_ROUTE)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_ROUTE)
                ->getRoute(),
            $this->routeBuilderFactory->createResourceTabRouteBuilder(static::EDIT_FORM_ROUTE, '/redirect-routes/:id')
                ->setResourceKey('redirect_routes')
                ->setBackRoute(static::LIST_ROUTE)
                ->setTitleProperty('name')
                ->getRoute(),
            $this->routeBuilderFactory->createFormRouteBuilder('sulu_redirects.edit_form.details', '/details')
                ->setResourceKey('redirect_routes')
                ->setFormKey('redirect_route_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_ROUTE)
                ->getRoute(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityContexts()
    {
        return [
            'Sulu' => [
                'Settings' => [
                    self::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                    ],
                ],
            ],
        ];
    }
}
