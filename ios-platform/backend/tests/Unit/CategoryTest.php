<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created(): void
    {
        $category = Category::create([
            'name'        => 'Productivity',
            'slug'        => 'productivity',
            'description' => 'Productivity apps',
            'is_active'   => true,
        ]);

        $this->assertDatabaseHas('categories', ['slug' => 'productivity']);
    }

    public function test_category_has_apps_relationship(): void
    {
        $category = Category::create([
            'name' => 'Games',
            'slug' => 'games',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->apps());
    }
}
