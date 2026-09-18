<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分自身の名前を維持したまま更新できること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => '質問',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_他で使用されているタグ名には変更できないこと(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Tag::factory()->create(['name' => '要望']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => '要望',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }
}
