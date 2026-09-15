<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\Contact;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しいデータで201とレコードが作成されること(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'category_id' => $category->id,
            'detail' => 'テストです',
            'tag_ids' => [],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);
    }
    public function test_バリデーションエラー時は422が返ること(): void
    {
        $data = [];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }
    public function test_詳細情報がJSON形式で返ること(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'email' => $contact->email,
            ],
        ]);
    }
    public function test_存在しないIDで404エラーが返ること(): void
    {
        $response = $this->getJson('/api/v1/contacts/9999');

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }
    public function test_一覧がページネーション付きで返ること(): void
    {
        Contact::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/contacts?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.total', 15);
    }
    public function test_キーワード検索で絞り込めること(): void
    {
        Contact::factory()->create(['first_name' => '山田']);
        Contact::factory()->create(['first_name' => '鈴木']);

        $response = $this->getJson('/api/v1/contacts?keyword=山田');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', '山田');
    }
    public function test_性別で絞り込めること(): void
    {
        Contact::factory()->create(['gender' => 1]);
        Contact::factory()->create(['gender' => 2]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.gender', 1);
    }
    public function test_更新できて200が返ること(): void
    {
        $contact = Contact::factory()->create();
        $category = Category::factory()->create();

        $data = [
            'first_name' => '更新後太郎',
            'last_name' => '更新後花子',
            'gender' => 2,
            'email' => 'updated@example.com',
            'tel' => '08012345678',
            'address' => '東京都新宿区',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '更新テストです',
            'tag_ids' => [],
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'updated@example.com',
        ]);
    }
    public function test_更新時バリデーションエラーで422が返ること(): void
    {
        $contact = Contact::factory()->create();

        $data = [];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_更新時存在しないIDで404が返ること(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'category_id' => $category->id,
            'detail' => 'テストです',
            'tag_ids' => [],
        ];

        $response = $this->putJson('/api/v1/contacts/9999', $data);

        $response->assertStatus(404);
    }
    public function test_削除できて204が返ること(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_削除時存在しないIDで404が返ること(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/9999');

        $response->assertStatus(404);
    }
}