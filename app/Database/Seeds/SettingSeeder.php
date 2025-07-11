<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\SettingModel;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settingModel = new SettingModel();

        $settings = [
            [
                'setting_key' => 'store_name',
                'setting_value' => 'Toko Khumaira Jaya',
            ],
            [
                'setting_key' => 'store_address',
                'setting_value' => 'Jl. Merdeka No. 45, Kota Bahagia, 12345',
            ],
            [
                'setting_key' => 'store_phone',
                'setting_value' => '0812-3456-7890',
            ],
            [
                'setting_key' => 'store_email',
                'setting_value' => 'info@tokokhumaira.com',
            ],
            [
                'setting_key' => 'receipt_footer_message',
                'setting_value' => 'Terima kasih telah berbelanja di Toko Khumaira!',
            ],
            // Add other default settings as needed
            // Example: default tax rate, currency symbol, etc.
            // [
            //     'setting_key' => 'currency_symbol',
            //     'setting_value' => 'Rp',
            // ],
        ];

        foreach ($settings as $setting) {
            // Check if the setting already exists to prevent duplicates on multiple seed runs
            $exists = $settingModel->where('setting_key', $setting['setting_key'])->first();
            if (!$exists) {
                $settingModel->insert($setting);
            } else {
                // Optionally update if it exists and you want to refresh the value on seed
                // $settingModel->update($exists->id, ['setting_value' => $setting['setting_value']]);
                log_message('info', "Setting '{$setting['setting_key']}' already exists, skipping insertion or use update logic.");
            }
        }
         // Output a message to the console
        echo "SettingSeeder run successfully.\n";
    }
}
