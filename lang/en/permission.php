<?php

use App\Enums\Permission\Name;

return [
    'name' => [
        Name::VIEW_USERS->value => 'View Users',
        Name::CREATE_USERS->value => 'Create Users',
        Name::EDIT_USERS->value => 'Edit Users',
        Name::DELETE_USERS->value => 'Delete Users',
        Name::VIEW_PLAYERS->value => 'View Players',
        Name::CREATE_PLAYERS->value => 'Create Players',
        Name::EDIT_PLAYERS->value => 'Edit Players',
        Name::DELETE_PLAYERS->value => 'Delete Players',
        Name::VIEW_ROLES->value => 'View Roles',
        Name::ASSIGN_ROLES->value => 'Assign Roles',
        Name::VIEW_PERMISSIONS->value => 'View Permissions',
        Name::MANAGE_PERMISSIONS->value => 'Manage Permissions',
    ],
];
