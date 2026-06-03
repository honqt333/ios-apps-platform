<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\App;
use App\Repositories\Eloquent\AppRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_apps_only(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        App::create([
            'name' => 'Active App', 'slug' => 'active-app', 'developer' => 'Dev',
            'bundle_id' => 'com.test.active', 'version' => '1.0',
            'minimum_ios_version' => '15.0', 'category_id' => $category->id,
            'is_active' => true, 'is_archived' => false,
        ]);

        App::create([
            'name' => 'Archived App', 'slug' => 'archived-app', 'developer' => 'Dev',
            'bundle_id' => 'com.test.archived', 'version' => '1.0',
            'minimum_ios_version' => '15.0', 'category_id' => $category->id,
            'is_active' => true, 'is_archived' => true,
        ]);

        $repo = new AppRepository(new App());
        $apps = $repo->listActive();

        $this->assertCount(1, $apps);
        $this->assertEquals('Active App', $apps->first()->name);
    }

    public function test_search_finds_apps_by_name(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        App::create([
            'name' => 'Awesome Photo Editor', 'slug' => 'awesome', 'developer' => 'Dev',
            'bundle_id' => 'com.test.awesome', 'version' => '1.0',
            'minimum_ios_version' => '15.0', 'category_id' => $category->id,
            'is_active' => true, 'is_archived' => false,
        ]);

        $repo = new AppRepository(new App());
        $result = $repo->search('photo');

        $this->assertGreaterThan(0, $result->total());
    }
}
