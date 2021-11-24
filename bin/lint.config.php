<?php

$finder = \PhpCsFixer\Finder::create()
    ->in([
        realpath(__DIR__ . '/../classes'),
    ]);

return (new \PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRules([
        '@PSR2' => true,
        // 'psr4' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
            ],
        ],
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => true,
        'blank_line_before_statement' => false,
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'single_quote' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                // 'magic',
                // 'phpunit',
                // 'method_public',
                // 'method_protected',
                // 'method_private',
            ],
        ],
    ])
    ->setFinder($finder);
