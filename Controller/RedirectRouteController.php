<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Ramsey\Uuid\Uuid;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManager;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API for redirect-routes.
 *
 * @RouteResource("redirect-route")
 */
class RedirectRouteController extends RestController implements ClassResourceInterface
{
    const RESULT_KEY = 'redirect-routes';

    /**
     * Returns columns for list.
     *
     * @return Response
     */
    public function fieldsAction()
    {
        return $this->handleView($this->view(array_values($this->getFieldDescriptors()), 200));
    }

    /**
     * Returns redirect-routes.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $restHelper = $this->get('sulu_core.doctrine_rest_helper');
        $factory = $this->get('sulu_core.doctrine_list_builder_factory');

        $listBuilder = $factory->create(RedirectRoute::class);
        $restHelper->initializeListBuilder($listBuilder, $this->getFieldDescriptors());
        $results = $listBuilder->execute();

        $list = new ListRepresentation(
            $results,
            self::RESULT_KEY,
            $request->attributes->get('_route'),
            $request->query->all(),
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($list));
    }

    /**
     * Create a new redirect-route.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        $serializer = $this->get('serializer');
        $redirectRoute = $serializer->deserialize(
            json_encode($data),
            $this->getParameter('sulu.model.redirect_route.class'),
            'json'
        );

        $this->getRedirectRouteManager()->save($redirectRoute);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view($redirectRoute));
    }

    /**
     * Returns single redirect-route.
     *
     * @param string $id
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function getAction($id)
    {
        $entity = $this->getRedirectRouteRepository()->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->getParameter('sulu.model.redirect_route.class'), $id);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * Create a new redirect-route.
     *
     * @param string $id
     * @param Request $request
     *
     * @return Response
     */
    public function putAction($id, Request $request)
    {
        $data = $request->request->all();
        $data['id'] = $id;

        $serializer = $this->get('serializer');
        $redirectRoute = $serializer->deserialize(
            json_encode($data),
            $this->getParameter('sulu.model.redirect_route.class'),
            'json'
        );

        $this->getRedirectRouteManager()->save($redirectRoute);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view($redirectRoute));
    }

    /**
     * Delete a redirect-route identified by id.
     *
     * @param string $id
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function deleteAction($id)
    {
        $redirectRoute = $this->getRedirectRouteRepository()->find($id);
        if (!$redirectRoute) {
            throw new EntityNotFoundException($this->getParameter('sulu.model.redirect_route.class'), $id);
        }

        $this->getRedirectRouteManager()->delete($redirectRoute);
        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view());
    }

    /**
     * Delete a list of redirect-route identified by id.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cdeleteAction(Request $request)
    {
        $repository = $this->getRedirectRouteRepository();
        $manager = $this->getRedirectRouteManager();

        $ids = array_filter(explode(',', $request->query->get('ids', '')));
        foreach ($ids as $id) {
            $redirectRoute = $repository->find($id);
            if (!$redirectRoute) {
                continue;
            }

            $manager->delete($redirectRoute);
        }

        $this->get('doctrine.orm.entity_manager')->flush();

        return $this->handleView($this->view());
    }

    /**
     * Returns redirect-route manager.
     *
     * @return RedirectRouteManager
     */
    protected function getRedirectRouteManager()
    {
        return $this->get('sulu_redirect.redirect_route_manager');
    }

    /**
     * Returns redirect-route repository.
     *
     * @return RedirectRouteRepositoryInterface
     */
    protected function getRedirectRouteRepository()
    {
        return $this->get('sulu.repository.redirect_route');
    }

    /**
     * @return FieldDescriptorInterface[]
     */
    private function getFieldDescriptors()
    {
        $factory = $this->get('sulu_core.list_builder.field_descriptor_factory');

        return $factory->getFieldDescriptorForClass(RedirectRoute::class);
    }
}
