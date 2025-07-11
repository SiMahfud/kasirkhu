<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CategoryModel;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categoryModel = new CategoryModel();

        $categories = [
            [
                'name' => 'ATK (Alat Tulis Kantor)',
                'description' => 'Berbagai macam alat tulis kantor.',
            ],
            [
                'name' => 'Jasa Fotokopi & Print',
                'description' => 'Layanan fotokopi dan pencetakan dokumen.',
            ],
            [
                'name' => 'Jasa Desain & Editing',
                'description' => 'Layanan desain grafis, editing dokumen, dan sejenisnya.',
            ],
            [
                'name' => 'Cetak Banner & Spanduk',
                'description' => 'Layanan pencetakan media besar seperti banner dan spanduk.',
            ],
            [
                'name' => 'Lain-lain',
                'description' => 'Produk atau jasa lain yang tidak masuk kategori di atas.',
            ]
        ];

        foreach ($categories as $category) {
            // Check if category already exists by name to avoid duplicates
            $exists = $categoryModel->where('name', $category['name'])->first();
            if (!$exists) {
                $categoryModel->insert($category);
            } else {
                 log_message('info', "Category '{$category['name']}' already exists, skipping insertion.");
            }
        }
        // echo "CategorySeeder run successfully.\n";
    }
}
