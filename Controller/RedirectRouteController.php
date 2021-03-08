<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\RedirectBundle\Admin\RedirectAdmin;
use Sulu\Bundle\RedirectBundle\Manager\RedirectRouteManagerInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteInterface;
use Sulu\Bundle\RedirectBundle\Model\RedirectRouteRepositoryInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\DoctrineRestHelper;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\ListRepresentation;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API for redirect-routes.
 *
 * @RouteResource("redirect-route")
 */
class RedirectRouteController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    const RESULT_KEY = 'redirect_routes';

    public function getSecurityContext(): string
    {
        return RedirectAdmin::SECURITY_CONTEXT;
    }

    /**
     * @var DoctrineRestHelper
     */
    protected $restHelper;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    protected $factory;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    protected $fieldDescriptor;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RedirectRouteManagerInterface
     */
    protected $redirectRouteManager;

    /**
     * @var RedirectRouteRepositoryInterface
     */
    protected $redirectRouteRepository;

    /**
     * @var string
     */
    protected $redirectRouteEntityName;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        DoctrineRestHelper $restHelper,
        DoctrineListBuilderFactoryInterface $factory,
        FieldDescriptorFactoryInterface $fieldDescriptor,
        EntityManagerInterface $entityManager,
        RedirectRouteManagerInterface $redirectRouteManager,
        RedirectRouteRepositoryInterface $redirectRouteRepository,
        string $redirectRouteEntityName
    ) {
        parent::__construct($viewHandler);
        $this->restHelper = $restHelper;
        $this->factory = $factory;
        $this->fieldDescriptor = $fieldDescriptor;
        $this->entityManager = $entityManager;
        $this->redirectRouteManager = $redirectRouteManager;
        $this->redirectRouteRepository = $redirectRouteRepository;
        $this->redirectRouteEntityName = $redirectRouteEntityName;
    }

    /**
     * Returns redirect-routes.
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        /** @var FieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptor->getFieldDescriptors('redirect_routes');
        $listBuilder = $this->factory->create($this->redirectRouteEntityName);

        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);
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
     * @return Response
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        $redirectRoute = $this->redirectRouteManager->saveByData($data);
        $this->entityManager->flush();

        return $this->handleView($this->view($redirectRoute));
    }

    public function postTriggerAction(Request $request, string $id): Response
    {
        $action = $request->get('action');

        /** @var RedirectRouteInterface $redirectRoute */
        $redirectRoute = $this->redirectRouteRepository->find($id);

        if (null === $redirectRoute) {
            throw new EntityNotFoundException($this->redirectRouteEntityName, $id);
        }

        try {
            switch ($action) {
                case 'enable':
                    $redirectRoute->setEnabled(true);
                    $this->entityManager->flush();

                    break;
                case 'disable':
                    $redirectRoute->setEnabled(false);
                    $this->entityManager->flush();

                    break;
                default:
                    throw new RestException('Unrecognized action: ' . $action);
            }

            // prepare view
            $view = $this->view($redirectRoute, 200);
        } catch (RestException $exception) {
            $view = $this->view($exception->toArray(), 400);
        }

        return $this->handleView($view);
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
        $entity = $this->redirectRouteRepository->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->redirectRouteEntityName, $id);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * Create a new redirect-route.
     *
     * @param string $id
     *
     * @return Response
     */
    public function putAction($id, Request $request)
    {
        $data = $request->request->all();
        $data['id'] = $id;

        $redirectRoute = $this->redirectRouteManager->saveByData($data);
        $this->entityManager->flush();

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
        /** @var RedirectRouteInterface|null $redirectRoute */
        $redirectRoute = $this->redirectRouteRepository->find($id);
        if (!$redirectRoute) {
            throw new EntityNotFoundException($this->redirectRouteEntityName, $id);
        }

        $this->redirectRouteManager->delete($redirectRoute);
        $this->entityManager->flush();

        return $this->handleView($this->view());
    }

    /**
     * Delete a list of redirect-route identified by id.
     *
     * @return Response
     */
    public function cdeleteAction(Request $request)
    {
        $repository = $this->redirectRouteRepository;
        $manager = $this->redirectRouteManager;

        $ids = array_filter(explode(',', (string) $request->query->get('ids', '')));
        foreach ($ids as $id) {
            /** @var RedirectRouteInterface|null $redirectRoute */
            $redirectRoute = $repository->find($id);
            if (!$redirectRoute) {
                continue;
            }

            $manager->delete($redirectRoute);
        }

        $this->entityManager->flush();

        return $this->handleView($this->view());
    }
}
