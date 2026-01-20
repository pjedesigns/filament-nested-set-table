<?php

namespace Pjedesigns\FilamentNestedSetTable\Services;

class MoveResult
{
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public ?int $newParentId = null,
        public ?int $newPosition = null,
        public bool $wasAutoAdjusted = false,
    ) {}

    public static function success(
        ?int $newParentId = null,
        ?int $newPosition = null,
        bool $wasAutoAdjusted = false,
    ): self {
        return new self(
            success: true,
            newParentId: $newParentId,
            newPosition: $newPosition,
            wasAutoAdjusted: $wasAutoAdjusted,
        );
    }

    public static function failure(string $error): self
    {
        return new self(
            success: false,
            error: $error,
        );
    }
}
