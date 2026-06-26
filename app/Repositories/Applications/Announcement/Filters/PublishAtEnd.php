<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Lte;

class PublishAtEnd extends Lte
{
    protected string $column = 'publish_at';
}
