<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$directories = [
    'app',
    'bootstrap',
    'config',
    'database',
    'lang',
    'routes',
    'tests',
];

$finder = Finder::create();

foreach ($directories as $directory) {
    $finder->in(__DIR__ . '/' . $directory);
}

$finder->notName('*.blade.php')
    ->exclude('cache');

$PSR12 = [
    'blank_line_after_opening_tag' => true,
    'compact_nullable_type_declaration' => true,
    'concat_space' => ['spacing' => 'one'],
    'declare_equal_normalize' => ['space' => 'none'],
    'new_with_parentheses' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
    ],
    'no_empty_statement' => true,
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_whitespace_in_blank_line' => true,
    'return_type_declaration' => ['space_before' => 'none'],
    'single_trait_insert_per_statement' => true,
];

$isCI = (bool) getenv('CI');

$config = (new Config())->setRules([
    '@PSR2' => true,
    '@Symfony' => true,
    'cast_spaces' => [
        'space' => 'none',
    ],
    'phpdoc_align' => [
        'tags' => ['method', 'property', 'return', 'throws', 'type', 'var'],
    ],
    'phpdoc_order' => true,
    'phpdoc_to_comment' => false,
    'ternary_to_null_coalescing' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'ordered_class_elements' => true,
    'array_syntax' => [
        'syntax' => 'short',
    ],
] + $PSR12)->setFinder($finder);

if (!$isCI) {
    $config->setUsingCache(true)
        ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
}

return $config;
