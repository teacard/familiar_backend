<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    public function forceFill(array $attributes): static
    {
        unset($attributes['last_used_at']);

        return parent::forceFill($attributes);
    }
}
