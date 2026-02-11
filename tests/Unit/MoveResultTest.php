<?php

declare(strict_types=1);

use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;

// ============================================
// MoveResult Factory Method Tests
// ============================================

it('success factory creates result with correct defaults', function () {
    $result = MoveResult::success();

    expect($result->success)->toBeTrue()
        ->and($result->error)->toBeNull()
        ->and($result->newParentId)->toBeNull()
        ->and($result->newPosition)->toBeNull()
        ->and($result->wasAutoAdjusted)->toBeFalse();
});

it('success factory accepts all parameters', function () {
    $result = MoveResult::success(
        newParentId: 10,
        newPosition: 3,
        wasAutoAdjusted: true,
    );

    expect($result->success)->toBeTrue()
        ->and($result->error)->toBeNull()
        ->and($result->newParentId)->toBe(10)
        ->and($result->newPosition)->toBe(3)
        ->and($result->wasAutoAdjusted)->toBeTrue();
});

it('success factory with null parent indicates root move', function () {
    $result = MoveResult::success(
        newParentId: null,
        newPosition: 0,
    );

    expect($result->success)->toBeTrue()
        ->and($result->newParentId)->toBeNull()
        ->and($result->newPosition)->toBe(0);
});

it('failure factory creates result with error message', function () {
    $result = MoveResult::failure('Something went wrong');

    expect($result->success)->toBeFalse()
        ->and($result->error)->toBe('Something went wrong')
        ->and($result->newParentId)->toBeNull()
        ->and($result->newPosition)->toBeNull()
        ->and($result->wasAutoAdjusted)->toBeFalse();
});

// ============================================
// MoveResult Constructor Tests
// ============================================

it('constructor sets all properties correctly', function () {
    $result = new MoveResult(
        success: true,
        error: null,
        newParentId: 5,
        newPosition: 2,
        wasAutoAdjusted: true,
    );

    expect($result->success)->toBeTrue()
        ->and($result->error)->toBeNull()
        ->and($result->newParentId)->toBe(5)
        ->and($result->newPosition)->toBe(2)
        ->and($result->wasAutoAdjusted)->toBeTrue();
});

it('constructor uses default values for optional parameters', function () {
    $result = new MoveResult(success: true);

    expect($result->error)->toBeNull()
        ->and($result->newParentId)->toBeNull()
        ->and($result->newPosition)->toBeNull()
        ->and($result->wasAutoAdjusted)->toBeFalse();
});

it('failure result can have attempted parent and position info', function () {
    $result = new MoveResult(
        success: false,
        error: 'Max depth exceeded',
        newParentId: 7,
        newPosition: 1,
    );

    expect($result->success)->toBeFalse()
        ->and($result->error)->toBe('Max depth exceeded')
        ->and($result->newParentId)->toBe(7)
        ->and($result->newPosition)->toBe(1);
});

it('success result with auto-adjustment flag', function () {
    $result = MoveResult::success(
        newParentId: 3,
        newPosition: 1,
        wasAutoAdjusted: true,
    );

    expect($result->wasAutoAdjusted)->toBeTrue()
        ->and($result->success)->toBeTrue();
});

it('success result without auto-adjustment flag defaults to false', function () {
    $result = MoveResult::success(
        newParentId: 3,
        newPosition: 1,
    );

    expect($result->wasAutoAdjusted)->toBeFalse();
});
