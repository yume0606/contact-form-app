<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('ja_JP');

        for ($i = 0; $i < 20; $i++) {
            $category = Category::inRandomOrder()->first();

            $contact = Contact::create([
                'category_id' => $category->id,
                'first_name' => $faker->lastName(),
                'last_name' => $faker->firstName(),
                'gender' => $faker->randomElement([1, 2, 3]),
                'email' => $faker->safeEmail(),
                'tel' => '0'.mt_rand(1000000000, 9999999999),
                'address' => $faker->address(),
                'building' => $faker->optional()->secondaryAddress(),
                'detail' => $faker->realText(100),
            ]);

            $tagIds = Tag::inRandomOrder()
                ->take(mt_rand(1, 3))
                ->pluck('id');

            $contact->tags()->attach($tagIds);
        }
    }
}
