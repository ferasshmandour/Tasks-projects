<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Traits\OwnershipTrait;

class PostPolicy
{
    use OwnershipTrait;

    /**
     * Determine if the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        return $this->isOwner($post);
    }

    /**
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->isOwner($post);
    }
}
