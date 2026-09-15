<?php

namespace Tests\Unit\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しい検索条件は全てバリデーションを通過する(): void
    {
        $category = Category::factory()->create();

        $data = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-01-15',
            'page' => 1,
            'per_page' => 10,
        ];

        $validator = Validator::make($data, (new IndexContactRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_検索条件が何も指定されなくてもバリデーションを通過する(): void
    {
        $data = [];

        $validator = Validator::make($data, (new IndexContactRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_不正な性別値は拒否すること(): void
    {
        $data = [
            'gender' => 4,
        ];

        $validator = Validator::make($data, (new IndexContactRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_存在しないカテゴリIDは拒否すること(): void
    {
        $data = [
            'category_id' => 9999,
        ];

        $validator = Validator::make($data, (new IndexContactRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}