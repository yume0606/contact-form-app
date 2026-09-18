<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは編集画面を表示できること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('質問');
    }

    public function test_認証済みユーザーはタグを作成できること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/admin/tags', ['name' => '新しいタグ']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['name' => '新しいタグ']);
    }

    public function test_認証済みユーザーはタグを更新できること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => '古い名前']);

        $response = $this->put("/admin/tags/{$tag->id}", ['name' => '新しい名前']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '新しい名前']);
    }

    public function test_認証済みユーザーはタグを削除できること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create();

        $response = $this->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_未認証ユーザーはタグ操作を拒否されログインページにリダイレクトされること(): void
    {
        $response = $this->post('/admin/tags', ['name' => 'テストタグ']);

        $response->assertRedirect('/login');
    }
}
