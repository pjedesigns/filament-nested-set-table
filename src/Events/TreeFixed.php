<?php

namespace Pjedesigns\FilamentNestedSetTable\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TreeFixed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $modelClass,
        public int $nodesFixed,
        public ?array $scopeAttributes = null,
    ) {}
}
