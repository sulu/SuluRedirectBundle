<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Integrates redirect-bundle into sulu-admin.
 */
class RedirectAdmin extends Admin
{
    const SECURITY_CONTEXT = 'sulu.modules.redirects';

    const LIST_VIEW = 'sulu_redirect.list';

    const ADD_FORM_VIEW = 'sulu_redirect.add_form';

    const EDIT_FORM_VIEW = 'sulu_redirect.edit_form';

    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

    /**
     * @var SecurityCheckerInterface
     */
    protected $securityChecker;

    /**
     * RedirectAdmin constructor.
     *
     * @param ViewBuilderFactoryInterface $viewBuilderFactory
     * @param SecurityCheckerInterface $securityChecker
     */
    public function __construct(ViewBuilderFactoryInterface $viewBuilderFactory, SecurityCheckerInterface $securityChecker)
    {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker = $securityChecker;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(self::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $redirect = new NavigationItem('sulu_redirect.title');
            $redirect->setPosition(51);
            $redirect->setView(static::LIST_VIEW);

            $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($redirect);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $formToolbarActions = [
            new ToolbarAction('sulu_admin.save'),
            new ToolbarAction('sulu_admin.delete'),
        ];

        $listToolbarActions = [
            new ToolbarAction('sulu_admin.add'),
            new ToolbarAction('sulu_admin.delete'),
        ];

        $viewCollection->add(
            $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/redirect-routes')
                ->setResourceKey('redirect_routes')
                ->setListKey('redirect_routes')
                ->setTitle('sulu_redirect.title')
                ->addListAdapters(['table'])
                ->setAddView(static::ADD_FORM_VIEW)
                ->setEditView(static::EDIT_FORM_VIEW)
                ->enableSearching()
                ->addToolbarActions($listToolbarActions)
        );

        $viewCollection->add(
            $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/redirect-routes/add')
                ->setResourceKey('redirect_routes')
                ->setBackView(static::LIST_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder('sulu_redirects.add_form.details', '/details')
                ->setResourceKey('redirect_routes')
                ->setFormKey('redirect_route_details')
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_VIEW)
        );

        $viewCollection->add($this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/redirect-routes/:id')
            ->setResourceKey('redirect_routes')
            ->setBackView(static::LIST_VIEW)
            ->setTitleProperty('name')
        );

        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder('sulu_redirects.edit_form.details', '/details')
                ->setResourceKey('redirect_routes')
                ->setFormKey('redirect_route_details')
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_VIEW)
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
