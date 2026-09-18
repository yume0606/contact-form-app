<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせフォームが表示されること(): void
    {
        $category = Category::factory()->create(['content' => '商品について']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品について');
        $response->assertSee('質問');
    }

    public function test_サンクスページが表示されること(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
    }

    public function test_認証済みユーザーは管理画面を表示できること(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_未認証ユーザーはログインページにリダイレクトされること(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_バリデーション通過時に確認ページが表示されること(): void
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

        $response = $this->post('/contacts/confirm', $data);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('山田');
        $response->assertSee('test@example.com');
    }

    public function test_バリデーションエラー時はリダイレクトされること(): void
    {
        $response = $this->post('/contacts/confirm', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_お問い合わせが保存されthanksへリダイレクトされること(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

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
            'tag_ids' => [$tag->id],
        ];

        $response = $this->post('/contacts', $data);

        $response->assertRedirect('/thanks');

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);

        $contact = Contact::where('email', 'test@example.com')->first();
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_お問い合わせ保存時バリデーションエラーでリダイレクトされること(): void
    {
        $response = $this->post('/contacts', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }
}
