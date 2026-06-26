<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Lt;

class ExpiresAtLt extends Lt
{
    protected string $column = 'expires_at';
}
