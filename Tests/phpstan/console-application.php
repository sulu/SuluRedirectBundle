<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Sulu\Bundle\RedirectBundle\Tests\Application\Kernel;

$kernel = new Kernel('test', false, Kernel::CONTEXT_ADMIN);
$kernel->boot();

return new Application($kernel);
