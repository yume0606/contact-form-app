<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグ名が空だと拒否されること(): void
    {
        $data = ['name' => ''];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_タグ名が51文字以上だと拒否されること(): void
    {
        $data = ['name' => str_repeat('あ', 51)];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_既に存在するタグ名は拒否されること(): void
    {
        Tag::factory()->create(['name' => '質問']);

        $data = ['name' => '質問'];

        $validator = Validator::make($data, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
    }
}
