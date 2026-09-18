<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせ一覧が7件ごとにページネーションされること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::factory()->count(10)->create();

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('contacts');

        $contacts = $response->viewData('contacts');
        $this->assertCount(7, $contacts);
    }

    public function test_キーワードで絞り込めること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::factory()->create(['first_name' => '山田']);
        Contact::factory()->create(['first_name' => '鈴木']);

        $response = $this->get('/admin?keyword=山田');

        $response->assertStatus(200);

        $contacts = $response->viewData('contacts');
        $this->assertCount(1, $contacts);
    }

    public function test_お問い合わせ詳細が表示されること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create(['content' => '商品について']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
        ]);

        $response = $this->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertSee('山田');
        $response->assertSee('商品について');
    }

    public function test_お問い合わせが削除され管理画面にリダイレクトされること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::factory()->create();

        $response = $this->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
