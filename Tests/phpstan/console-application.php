<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Sulu\Bundle\RedirectBundle\Tests\Application\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

$kernel = new Kernel('test', false, Kernel::CONTEXT_ADMIN);
$kernel->boot();

return new Application($kernel);
