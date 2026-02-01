<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('currentUserId')) {
    /**
     * Get the ID of the currently authenticated user.
     */
    function currentUserId(): ?int
    {
        return Auth::id();
    }
}
