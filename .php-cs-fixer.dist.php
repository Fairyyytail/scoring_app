<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;
$instance = new PhpCsFixer\Config();

return $instance
    ->setUnsupportedPhpVersionAllowed(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect(processTimeout: 240))
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'continue',
                'do',
                'exit',
                'goto',
                'if',
                'return',
                'switch',
                'throw',
                'try',
            ],
        ],
        'declare_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'php_unit_internal_class' => false,
        'php_unit_strict' => false,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_to_comment' => false,
        'yoda_style' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arrays', 'arguments']
        ],
        'ordered_imports' =>['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'fully_qualified_strict_types'=>true,
        'no_unused_imports'=> true,
        'type_declaration_spaces'=>['elements'=>['function', 'property']],
        'single_quote' => [
            'strings_containing_single_quote_chars' => false,
        ],
        'no_extra_blank_lines'=> ['tokens' => ['attribute', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'switch', 'throw', 'use']],
        'nullable_type_declaration_for_default_null_value' => true,
        'new_expression_parentheses'=> ['use_parentheses' => false],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache');