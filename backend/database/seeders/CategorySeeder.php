<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Productivity', 'icon' => 'briefcase', 'color' => '#3B82F6'],
            ['name' => 'Games',        'icon' => 'gamepad',   'color' => '#EF4444'],
            ['name' => 'Utilities',    'icon' => 'wrench',    'color' => '#10B981'],
            ['name' => 'Social',       'icon' => 'users',     'color' => '#8B5CF6'],
            ['name' => 'Education',    'icon' => 'book',      'color' => '#F59E0B'],
            ['name' => 'Entertainment','icon' => 'film',      'color' => '#EC4899'],
            ['name' => 'Health',       'icon' => 'heart',     'color' => '#06B6D4'],
            ['name' => 'Photography',  'icon' => 'camera',    'color' => '#F97316'],
            ['name' => 'Music',        'icon' => 'music',     'color' => '#6366F1'],
            ['name' => 'Developer Tools', 'icon' => 'code',  'color' => '#0EA5E9'],
        ];

        foreach ($items as $i => $item) {
            Category::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name'        => $item['name'],
                    'description' => $item['name'] . ' applications',
                    'icon'        => $item['icon'],
                    'color'       => $item['color'],
                    'sort_order'  => $i,
                    'is_active'   => true,
                ]
            );
        }
    }
}
