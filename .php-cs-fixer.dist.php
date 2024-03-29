<?php

$header = <<<EOF
This file is part of Sulu.

(c) Sulu GmbH

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude(['var/cache'])
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
$config
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'class_definition' => false,
        'concat_space' => ['spacing' => 'one'],
        'function_declaration' => ['closure_function_spacing' => 'none'],
        'header_comment' => ['header' => $header],
        'ordered_imports' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_types_order' => false,
    ])
    ->setFinder($finder);

return $config;
