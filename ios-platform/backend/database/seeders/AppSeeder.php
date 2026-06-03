<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\App;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@platform.local')->first();
        $categories = Category::all();

        $samples = [
            [
                'name'              => 'Pro Tasks',
                'developer'         => 'Pro Studio',
                'description'       => 'Powerful task management with cloud sync.',
                'long_description'  => 'Pro Tasks is a sleek, fast, and reliable task manager for iPhone and iPad. Organize your day, set reminders, and collaborate with your team.',
                'version'           => '1.4.2',
                'build_number'      => '142',
                'minimum_ios_version' => '15.0',
                'category'          => 'Productivity',
                'is_featured'       => true,
                'changelog'         => "• Bug fixes\n• New widgets\n• Improved performance",
            ],
            [
                'name'              => 'Pixel Studio',
                'developer'         => 'Creative Lab',
                'description'       => 'Photo editor with AI-powered filters.',
                'long_description'  => 'Transform your photos with a tap. Pixel Studio uses advanced AI to enhance, retouch, and restyle your images instantly.',
                'version'           => '2.0.1',
                'build_number'      => '201',
                'minimum_ios_version' => '16.0',
                'category'          => 'Photography',
                'is_featured'       => true,
                'changelog'         => "• Redesigned UI\n• 50+ new filters\n• iPad support",
            ],
            [
                'name'              => 'FitMate',
                'developer'         => 'Healthify',
                'description'       => 'Personal fitness and nutrition tracker.',
                'version'           => '3.1.0',
                'build_number'      => '310',
                'minimum_ios_version' => '14.0',
                'category'          => 'Health',
                'is_featured'       => true,
            ],
            [
                'name'              => 'Codex',
                'developer'         => 'DevShop',
                'description'       => 'Markdown notes for developers.',
                'version'           => '1.0.0',
                'build_number'      => '100',
                'minimum_ios_version' => '15.0',
                'category'          => 'Developer Tools',
            ],
            [
                'name'              => 'Lumen Player',
                'developer'         => 'Lumen Inc',
                'description'       => 'Premium music player with lossless support.',
                'version'           => '5.2.0',
                'build_number'      => '520',
                'minimum_ios_version' => '16.0',
                'category'          => 'Music',
            ],
            [
                'name'              => 'Quick Chat',
                'developer'         => 'Social Apps',
                'description'       => 'Lightweight messaging app with end-to-end encryption.',
                'version'           => '4.0.3',
                'build_number'      => '403',
                'minimum_ios_version' => '15.0',
                'category'          => 'Social',
            ],
        ];

        foreach ($samples as $i => $sample) {
            $slug = Str::slug($sample['name']);
            $category = $categories->firstWhere('slug', Str::slug($sample['category']));

            $app = App::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'                => $sample['name'],
                    'developer'           => $sample['developer'],
                    'description'         => $sample['description'],
                    'long_description'    => $sample['long_description'] ?? $sample['description'],
                    'bundle_id'           => 'com.platform.' . $slug,
                    'version'             => $sample['version'],
                    'build_number'        => $sample['build_number'] ?? null,
                    'minimum_ios_version' => $sample['minimum_ios_version'],
                    'category_id'         => $category?->id,
                    'is_active'           => true,
                    'is_featured'         => $sample['is_featured'] ?? false,
                    'is_archived'         => false,
                    'changelog'           => $sample['changelog'] ?? null,
                    'downloads_count'     => fake()->numberBetween(50, 5000),
                    'created_by'          => $admin?->id,
                    'updated_by'          => $admin?->id,
                ]
            );
        }
    }
}
