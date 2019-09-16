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
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\Routing\RouteBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Routing\RouteCollection;
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

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(self::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $redirect = new NavigationItem('sulu_redirect.title');
            $redirect->setPosition(51);
            $redirect->setMainRoute(static::LIST_ROUTE);

            $navigationItemCollection->add($redirect);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getJsBundleName()
    {
        return 'suluredirect';
    }

    public function configureRoutes(RouteCollection $routeCollection): void
    {
        $formToolbarActions = [
            'sulu_admin.save',
            'sulu_admin.delete',
        ];

        $listToolbarActions = [
            'sulu_admin.add',
            'sulu_admin.delete'
        ];

        $routeCollection->add(
            $this->routeBuilderFactory->createListRouteBuilder(static::LIST_ROUTE, '/redirect-routes')
                ->setResourceKey('redirect_routes')
                ->setListKey('redirect_routes')
                ->setTitle('sulu_redirect.title')
                ->addListAdapters(['table'])
                ->setAddRoute(static::ADD_FORM_ROUTE)
                ->setEditRoute(static::EDIT_FORM_ROUTE)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
        );

        $routeCollection->add(
            $this->routeBuilderFactory->createResourceTabRouteBuilder(static::ADD_FORM_ROUTE, '/redirect-routes/add')
                ->setResourceKey('redirect_routes')
                ->setBackRoute(static::LIST_ROUTE)
        );

        $routeCollection->add(
            $this->routeBuilderFactory->createFormRouteBuilder('sulu_redirects.add_form.details', '/details')
                ->setResourceKey('redirect_routes')
                ->setFormKey('redirect_route_details')
                ->setTabTitle('sulu_admin.details')
                ->setEditRoute(static::EDIT_FORM_ROUTE)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_ROUTE)
        );

        $routeCollection->add($this->routeBuilderFactory->createResourceTabRouteBuilder(static::EDIT_FORM_ROUTE, '/redirect-routes/:id')
            ->setResourceKey('redirect_routes')
            ->setBackRoute(static::LIST_ROUTE)
            ->setTitleProperty('name')
        );

        $routeCollection->add(
            $this->routeBuilderFactory->createFormRouteBuilder('sulu_redirects.edit_form.details', '/details')
                ->setResourceKey('redirect_routes')
                ->setFormKey('redirect_route_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_ROUTE)
        );
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
