<?php

namespace App\Policies;

use App\Models\{ChangeRequest, User};

class ChangeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDirector();
    }

    public function view(User $user, ChangeRequest $changeRequest): bool
    {
        return $user->isDirector() || $changeRequest->requested_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isHR();
    }

    public function review(User $user, ChangeRequest $changeRequest): bool
    {
        return $user->isDirector() && $changeRequest->requested_by !== $user->id;
    }
}
