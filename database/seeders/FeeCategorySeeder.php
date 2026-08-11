<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeCategory;

class FeeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tuition',            'slug' => 'tuition',             'sort_order' => 1],
            ['name' => 'Examination',        'slug' => 'examination',         'sort_order' => 2],
            ['name' => 'Development Levy',   'slug' => 'development-levy',    'sort_order' => 3],
            ['name' => 'Uniform & Sports',   'slug' => 'uniform-sports',      'sort_order' => 4],
            ['name' => 'Other',              'slug' => 'other',               'sort_order' => 99],
        ];

        foreach ($categories as $cat) {
            FeeCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
