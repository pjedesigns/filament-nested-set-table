<?php

namespace Pjedesigns\FilamentNestedSetTable\Actions;

use Closure;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class TreeDeleteAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->modalDescription(function (Model $record): ?string {
            $descendantCount = $this->getDescendantCount($record);

            if ($descendantCount === 0) {
                return __('filament-nested-set-table::actions.delete_confirm');
            }

            return trans_choice(
                'filament-nested-set-table::actions.delete_confirm_with_children',
                $descendantCount,
                ['count' => $descendantCount]
            );
        });
    }

    protected function getDescendantCount(Model $record): int
    {
        if (! method_exists($record, 'descendants')) {
            return 0;
        }

        return $record->descendants()->count();
    }
}
