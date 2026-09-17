<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Validator;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しいデータは全てバリデーションを通過する(): void
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

        $validator = Validator::make($data, (new StoreContactRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_不正な電話番号形式は拒否すること(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '123',
            'address' => '東京都渋谷区',
            'building' => null,
            'category_id' => $category->id,
            'detail' => 'テストです',
            'tag_ids' => [],
        ];

        $validator = Validator::make($data, (new StoreContactRequest())->rules());

        $this->assertTrue($validator->fails());
    }
    public function test_タグ入力を受け付ける(): void
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

        $validator = Validator::make($data, (new StoreContactRequest())->rules());

        $this->assertFalse($validator->fails());
    }
}
