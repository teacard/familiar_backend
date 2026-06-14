<?php

namespace Database\Seeders;

use App\Enums\Permission\Name;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $defined  = collect(Name::cases())->pluck('value');
        $existing = Permission::pluck('name');

        Permission::insert(
            $defined->diff($existing)
                ->map(fn(string $name) => [
                    'name'       => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->values()
                ->all()
        );

        Permission::whereIn('name', $existing->diff($defined))->delete();
    }
}
