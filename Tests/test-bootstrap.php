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

require __DIR__ . '/Application/config/bootstrap.php';

if (!\trait_exists(Prophecy\PhpUnit\ProphecyTrait::class)) { // backwards compatibility layer for < PHP 7.3
    require __DIR__ . '/prophecy-trait-bc-layer.php';
}
