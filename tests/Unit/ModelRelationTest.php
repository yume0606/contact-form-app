<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_カテゴリから紐づく複数のお問い合わせが取得できること(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->contacts);
    }

    public function test_お問い合わせが特定のカテゴリに属しタグと同期できること(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $contact->tags()->sync([$tag1->id, $tag2->id]);

        $this->assertTrue($contact->category->is($category));
        $this->assertCount(2, $contact->tags);
    }

    public function test_タグが複数のお問い合わせに紐づいていること(): void
    {
        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(3)->create();

        foreach ($contacts as $contact) {
            $contact->tags()->attach($tag->id);
        }

        $this->assertCount(3, $tag->contacts);
    }
}
