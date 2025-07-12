<?php

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class SettingController extends BaseController
{
    protected $settingModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        if (!auth()->user()->can('admin.settings')) {
            return redirect()->to(base_url())->with('error', 'You do not have permission to access this page.');
        }

        $settingsKeys = ['store_name', 'store_address', 'store_phone', 'receipt_footer_message'];
        $data['settings'] = $this->settingModel->getSettings($settingsKeys);
        $data['title'] = 'Store Settings';

        return view('settings/index', $data);
    }

    public function update()
    {
        if (!auth()->user()->can('admin.settings')) {
             return redirect()->to(base_url())->with('error', 'You do not have permission to perform this action.');
        }

        $rules = [
            'store_name' => 'permit_empty|string|max_length[255]',
            'store_address' => 'permit_empty|string',
            'store_phone' => 'permit_empty|string|max_length[50]',
            'receipt_footer_message' => 'permit_empty|string|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingsToUpdate = [
            'store_name' => $this->request->getPost('store_name'),
            'store_address' => $this->request->getPost('store_address'),
            'store_phone' => $this->request->getPost('store_phone'),
            'receipt_footer_message' => $this->request->getPost('receipt_footer_message'),
        ];

        $success = true;
        foreach ($settingsToUpdate as $key => $value) {
            if (!$this->settingModel->saveSetting($key, $value === null ? '' : $value)) {
                $success = false;
                log_message('error', "Failed to save setting: {$key}");
            }
        }

        if ($success) {
            return redirect()->to('admin/settings')->with('message', 'Settings updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update some settings. Please check the logs.');
        }
    }
}
