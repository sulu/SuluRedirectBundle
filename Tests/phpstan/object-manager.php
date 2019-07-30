<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sulu\Bundle\RedirectBundle\Tests\Application\Kernel;

$kernel = new Kernel('test', false, Kernel::CONTEXT_ADMIN);
$kernel->boot();

/** @var ContainerInterface $container */
$container = $kernel->getContainer();

return $container->get('doctrine')->getManager();
