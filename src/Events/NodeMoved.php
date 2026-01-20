<?php

namespace Pjedesigns\FilamentNestedSetTable\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Pjedesigns\FilamentNestedSetTable\Services\MoveResult;

class NodeMoved implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Model $node,
        public MoveResult $result,
        public ?int $previousParentId = null,
        public ?int $previousPosition = null,
    ) {}

    public function broadcastOn(): array
    {
        if (! config('filament-nested-set-table.broadcast_enabled')) {
            return [];
        }

        $model = class_basename($this->node);

        return ["tree-updates.{$model}"];
    }

    public function broadcastAs(): string
    {
        return 'node.moved';
    }

    public function broadcastWith(): array
    {
        return [
            'nodeId' => $this->node->getKey(),
            'newParentId' => $this->result->newParentId,
            'newPosition' => $this->result->newPosition,
            'wasAutoAdjusted' => $this->result->wasAutoAdjusted,
        ];
    }
}
