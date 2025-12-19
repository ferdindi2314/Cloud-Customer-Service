<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $items = [
            ['name' => 'Bug', 'description' => 'Bug / Error reports'],
            ['name' => 'Permintaan Fitur', 'description' => 'Feature requests and improvements'],
            ['name' => 'Tanya Jawab', 'description' => 'General questions and support'],
            ['name' => 'Pembayaran', 'description' => 'Billing and payments'],
        ];

        foreach ($items as $item) {
            Category::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                ['name' => $item['name'], 'description' => $item['description']]
            );
        }
    }
}
