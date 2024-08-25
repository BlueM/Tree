<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('node_modules')
    ->exclude('var')
;

$config = new PhpCsFixer\Config();
$config
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRules([
        '@Symfony' => true,
        'single_line_throw' => false,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        PhpCsFixerCustomFixers\Fixer\CommentSurroundedBySpacesFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer::name() => [
            'minimum_number_of_parameters' => 2,
            'keep_blank_lines' => true,
        ],
        PhpCsFixerCustomFixers\Fixer\NoDoctrineMigrationsGeneratedCommentFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoDuplicatedArrayKeyFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoDuplicatedImportsFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoImportFromGlobalNamespaceFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoLeadingSlashInGlobalNamespaceFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoSuperfluousConcatenationFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoTrailingCommaInSinglelineFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoUselessCommentFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoUselessDirnameCallFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoUselessDoctrineRepositoryCommentFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\PhpdocArrayStyleFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\PhpdocNoSuperfluousParamFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\PhpdocSelfAccessorFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\PhpdocTypesCommaSpacesFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\PhpdocTypesTrimFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\SingleSpaceAfterStatementFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\SingleSpaceBeforeStatementFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\StringableInterfaceFixer::name() => true,
    ])
    ->setFinder($finder)
;

return $config;
