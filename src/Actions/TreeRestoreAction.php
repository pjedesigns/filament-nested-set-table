<?php

namespace Pjedesigns\FilamentNestedSetTable\Actions;

use Filament\Actions\RestoreAction;
use Illuminate\Database\Eloquent\Model;

class TreeRestoreAction extends RestoreAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->modalDescription(function (Model $record): ?string {
            $descendantCount = $this->getTrashedDescendantCount($record);

            if ($descendantCount === 0) {
                return __('filament-nested-set-table::actions.restore_confirm');
            }

            return trans_choice(
                'filament-nested-set-table::actions.restore_confirm_with_children',
                $descendantCount,
                ['count' => $descendantCount]
            );
        });
    }

    protected function getTrashedDescendantCount(Model $record): int
    {
        if (! method_exists($record, 'descendants')) {
            return 0;
        }

        // Count only trashed descendants that will be restored
        // The nested set package restores descendants deleted at the same time or after
        $deletedAt = $record->{$record->getDeletedAtColumn()};

        if (! $deletedAt) {
            return 0;
        }

        return $record->descendants()
            ->onlyTrashed()
            ->where($record->getDeletedAtColumn(), '>=', $deletedAt)
            ->count();
    }
}
