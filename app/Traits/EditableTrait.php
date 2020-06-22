<?php

namespace App\Traits;

use App\Objects\User;

trait EditableTrait
{
    public function hasAccess(User $user)
    {
        if ($user == $this->created_by) return true;
        $find = $this->editors()->where([
            'user_id' => $user->id
        ])->first();

        if ($find) return true;

        return false;
    }
}
