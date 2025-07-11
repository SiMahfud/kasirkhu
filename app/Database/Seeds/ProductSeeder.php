<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\ProductModel;
use App\Models\CategoryModel;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();

        // Get category IDs (assuming CategorySeeder has run)
        $catAtk = $categoryModel->where('name', 'ATK (Alat Tulis Kantor)')->first();
        $catFotokopiPrint = $categoryModel->where('name', 'Jasa Fotokopi & Print')->first();
        $catDesainEdit = $categoryModel->where('name', 'Jasa Desain & Editing')->first();
        $catBanner = $categoryModel->where('name', 'Cetak Banner & Spanduk')->first();

        $products = [];

        if ($catAtk) {
            $products = array_merge($products, [
                ['name' => 'Pulpen Standard AE7', 'code' => 'ATK001', 'category_id' => $catAtk->id, 'price' => 2500, 'unit' => 'pcs', 'stock' => 100],
                ['name' => 'Buku Tulis Sinar Dunia 38lbr', 'code' => 'ATK002', 'category_id' => $catAtk->id, 'price' => 3000, 'unit' => 'pcs', 'stock' => 50],
                ['name' => 'Pensil 2B Faber-Castell', 'code' => 'ATK003', 'category_id' => $catAtk->id, 'price' => 4000, 'unit' => 'pcs', 'stock' => 75],
                ['name' => 'Kertas HVS A4 70gr (Rim)', 'code' => 'ATK004', 'category_id' => $catAtk->id, 'price' => 45000, 'unit' => 'rim', 'stock' => 20],
            ]);
        }

        if ($catFotokopiPrint) {
            $products = array_merge($products, [
                ['name' => 'Fotokopi Hitam Putih A4/F4', 'code' => 'JFP001', 'category_id' => $catFotokopiPrint->id, 'price' => 250, 'unit' => 'lembar', 'stock' => 0], // Stok 0 untuk jasa
                ['name' => 'Print Warna A4 HVS', 'code' => 'JFP002', 'category_id' => $catFotokopiPrint->id, 'price' => 1000, 'unit' => 'lembar', 'stock' => 0],
                ['name' => 'Print Hitam Putih A4 HVS', 'code' => 'JFP003', 'category_id' => $catFotokopiPrint->id, 'price' => 500, 'unit' => 'lembar', 'stock' => 0],
            ]);
        }

        if ($catDesainEdit) {
            $products = array_merge($products, [
                ['name' => 'Jasa Desain Grafis', 'code' => 'JDE001', 'category_id' => $catDesainEdit->id, 'price' => 0, 'unit' => 'project', 'stock' => 0], // Stok 0, Harga manual
                ['name' => 'Jasa Edit Dokumen', 'code' => 'JDE002', 'category_id' => $catDesainEdit->id, 'price' => 0, 'unit' => 'dokumen', 'stock' => 0], // Stok 0, Harga manual
            ]);
        }

        if ($catBanner) {
            $products = array_merge($products, [
                 ['name' => 'Cetak Banner Flexi China', 'code' => 'JCB001', 'category_id' => $catBanner->id, 'price' => 25000, 'unit' => 'm2', 'stock' => 0], // Stok 0, Harga per meter persegi
            ]);
        }

        foreach ($products as $product) {
             // Check if product already exists by code to avoid duplicates
            $exists = null;
            if (!empty($product['code'])) {
                $exists = $productModel->where('code', $product['code'])->first();
            } else {
                // If code is empty, check by name and category to be safer for jasa type
                 $exists = $productModel->where('name', $product['name'])
                                        ->where('category_id', $product['category_id'])
                                        ->first();
            }

            if (!$exists) {
                $productModel->insert($product);
            } else {
                 log_message('info', "Product '{$product['name']}' (Code: {$product['code']}) already exists, skipping insertion.");
            }
        }
        // echo "ProductSeeder run successfully.\n";
    }
}
