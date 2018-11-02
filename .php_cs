<?php
$header = <<<'EOF'
@copyright 2018 Hilmi Erdem KEREN
@license MIT
EOF;
$excludedFolders = [
    'scripts',
    'vendor',
    'config',
];

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        // Migration and Presets
        '@PHP56Migration' => true,
        '@PHPUnit60Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // Laravel
        'phpdoc_align' => true,
        'binary_operator_spaces' => [
            'align_double_arrow' => true,
            'align_equals' => false,
        ],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_return' => true,
        'braces' => true,
        'cast_spaces' => [
             'space' => 'single',
         ],
        'class_definition' => [
            'single_line' => true,
        ],
        'concat_space' => [
            'spacing' => 'none',
        ],
        'declare_equal_normalize' => true,
        'elseif' => true,
        'encoding' => true,
        'full_opening_tag' => true,
        'function_declaration' => true,
        'function_typehint_space' => true,
        'hash_to_slash_comment' => true,
        'heredoc_to_nowdoc' => true,
        'include' => true,
        'indentation_type' => true,
        'ordered_imports' => [
            'sortAlgorithm' => 'length',
        ],
        'lowercase_cast' => true,
        'lowercase_constants' => true,
        'lowercase_keywords' => true,
        'magic_constant_casing' => true,
        'method_argument_space' => true,
        'class_attributes_separation' => [
            'elements' => ['method', 'property'],
        ],
        'visibility_required' => true,
        'native_function_casing' => true,
        'no_alias_functions' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_closing_tag' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => [
            'positions' => ['inside', 'outside'],
        ],
        'no_spaces_inside_parenthesis' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_no_alias_tag' => [
            'replacements' => [
                'property-read' => 'property',
                'property-write' => 'property',
                'type' => 'var',
                'link' => 'see',
            ]
        ],
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'no_mixed_echo_print' => true,
        'psr4' => true, // risky!
        'self_accessor' => true,
        'array_syntax' => ['syntax' => 'short'],
        'short_scalar_cast' => true,
        'simplified_null_return' => true,
        'single_blank_line_at_eof' => true,
        'single_blank_line_before_namespace' => true,
        'single_class_element_per_statement' => true,
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline_array' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => false,
        'line_ending' => true,
        'whitespace_after_comma_in_array' => true,

        // Customization
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_like'
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'compact_nullable_typehint' => true,
        'escape_implicit_backslashes' => true,
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,

        'final_internal_class' => true,
        'list_syntax' => ['syntax' => 'long'],
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break', 'continue', 'extra', 'return', 'throw', 'use', 'use_trait',
                'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block'
            ]
        ],
        'no_null_property_initialization' => true,
        'no_short_echo_tag' => true,
        'no_superfluous_elseif' => true,
        'no_unneeded_curly_braces' => true,
        'no_unneeded_final_method' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'php_unit_strict' => true,
        'php_unit_test_annotation' => true,
        'php_unit_test_class_requires_covers' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'phpdoc_types_order' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'yoda_style' => true,

        // Customization
        'header_comment' => ['header' => $header],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude($excludedFolders)
            ->notName('server.php')
            ->in(__DIR__)
    )
;

try {
    PhpCsFixer\FixerFactory::create()
        ->registerBuiltInFixers()
        ->registerCustomFixers($config->getCustomFixers())
        ->useRuleSet(new PhpCsFixer\RuleSet($config->getRules()));
} catch (PhpCsFixer\ConfigurationException\InvalidConfigurationException $e) {
    $config->setRules([]);
} catch (UnexpectedValueException $e) {
    $config->setRules([]);
} catch (InvalidArgumentException $e) {
    $config->setRules([]);
}

return $config;
