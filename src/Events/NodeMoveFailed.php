<?php

namespace Pjedesigns\FilamentNestedSetTable\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NodeMoveFailed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Model $node,
        public string $error,
        public ?int $attemptedParentId = null,
        public ?int $attemptedPosition = null,
    ) {}
}
