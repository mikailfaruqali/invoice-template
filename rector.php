<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use RectorLaravel\Rector\ArrayDimFetch\EnvVariableToEnvHelperRector;
use RectorLaravel\Rector\Class_\AnonymousMigrationsRector;
use RectorLaravel\Rector\Coalesce\ApplyDefaultInsteadOfNullCoalesceRector;
use RectorLaravel\Rector\Empty_\EmptyToBlankAndFilledFuncRector;
use RectorLaravel\Rector\FuncCall\NotFilledBlankFuncCallToBlankFilledFuncCallRector;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;
use RectorLaravel\Rector\MethodCall\RedirectBackToBackHelperRector;
use RectorLaravel\Rector\MethodCall\RedirectRouteToToRouteHelperRector;
use RectorLaravel\Rector\MethodCall\WhereToWhereLikeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([
        'src',
    ])
    ->withSkip([
        RemoveUnusedVariableAssignRector::class,
        RemoveExtraParametersRector::class,
    ])
    ->withPreparedSets(
        deadCode: TRUE,
        codeQuality: TRUE,
        codingStyle: TRUE,
        privatization: TRUE,
        naming: TRUE,
    )
    ->withRules([
        AnonymousMigrationsRector::class,
        ApplyDefaultInsteadOfNullCoalesceRector::class,
        EmptyToBlankAndFilledFuncRector::class,
        EnvVariableToEnvHelperRector::class,
        NotFilledBlankFuncCallToBlankFilledFuncCallRector::class,
        RedirectBackToBackHelperRector::class,
        RedirectRouteToToRouteHelperRector::class,
        RemoveDumpDataDeadCodeRector::class,
        WhereToWhereLikeRector::class,
    ])
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
    ])
    ->withPhpSets(
        php83: TRUE,
    );
