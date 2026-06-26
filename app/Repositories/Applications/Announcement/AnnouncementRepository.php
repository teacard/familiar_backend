<?php

namespace App\Repositories\Applications\Announcement;

use App\Models\Announcement;
use App\Repositories\Repository;

class AnnouncementRepository extends Repository
{
    public function __construct(Announcement $model)
    {
        $this->model = $model;
    }
}
