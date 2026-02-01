<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait OwnershipTrait
{
    /**
     * Check if the authenticated user owns the model.
     */
    public function isOwner(Model $model): bool
    {
        return Auth::id() === $model->user_id;
    }
}
