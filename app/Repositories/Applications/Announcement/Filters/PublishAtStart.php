<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Gte;

class PublishAtStart extends Gte
{
    protected string $column = 'publish_at';
}
