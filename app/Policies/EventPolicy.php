<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return (int) $event->owner_user_id === (int) $user->id;
    }

    public function update(User $user, Event $event): bool
    {
        return (int) $event->owner_user_id === (int) $user->id;
    }
}
