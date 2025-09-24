<?php

namespace App\Observers;

use App\Models\Supports\SupportModel;
use App\Models\Supports\LogModel;
use Illuminate\Support\Facades\Auth;

class SupportObserver
{
    /**
     * Handle the SupportModel "created" event.
     */
    public function created(SupportModel $support): void
    {
        LogModel::query()->create([
            'support_id' => $support->id,
            'user_id' => Auth::id(),
            'action' => 'create',
            'before' => null,
            'after' => $support->getAttributes(),
        ]);
    }

    /**
     * Handle the SupportModel "updated" event.
     */
    public function updated(SupportModel $support): void
    {
        LogModel::query()->create([
            'support_id' => $support->id,
            'user_id' => Auth::id(),
            'action' => 'update',
            'before' => $support->getOriginal(),
            'after' => $support->getAttributes(),
        ]);
    }

    /**
     * Handle the SupportModel "deleted" event.
     */
    public function deleted(SupportModel $support): void
    {
        LogModel::query()->create([
            'support_id' => $support->id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'before' => $support->getOriginal(),
            'after' => null,
        ]);
    }

    /**
     * Handle the SupportModel "restored" event.
     */
    public function restored(SupportModel $supportModel): void
    {
        //
    }

    /**
     * Handle the SupportModel "force deleted" event.
     */
    public function forceDeleted(SupportModel $supportModel): void
    {
        //
    }
}
