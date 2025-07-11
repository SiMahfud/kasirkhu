<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object'; // Changed to object for consistency with other models
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Not using soft deletes for settings

    // Validation
    protected $validationRules      = [
        'setting_key' => 'required|is_unique[settings.setting_key,id,{id}]|max_length[100]',
        'setting_value' => 'permit_empty|string'
    ];
    protected $validationMessages   = [
        'setting_key' => [
            'required' => 'Setting key is required.',
            'is_unique' => 'Setting key must be unique.',
            'max_length' => 'Setting key cannot exceed 100 characters.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get a specific setting value by its key.
     *
     * @param string $key The setting key.
     * @return string|null The setting value or null if not found.
     */
    public function getSetting(string $key): ?string
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : null;
    }

    /**
     * Get multiple settings as an associative array.
     *
     * @param array $keys Array of setting keys to retrieve.
     * @return array Associative array of [key => value].
     */
    public function getSettings(array $keys): array
    {
        $settings = $this->whereIn('setting_key', $keys)->findAll();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->setting_key] = $setting->setting_value;
        }
        return $result;
    }

    /**
     * Save a setting. If it exists, update it. If not, create it.
     *
     * @param string $key The setting key.
     * @param string $value The setting value.
     * @return bool True on success, false on failure.
     */
    public function saveSetting(string $key, string $value): bool
    {
        $existing = $this->where('setting_key', $key)->first();
        if ($existing) {
            return $this->update($existing->id, ['setting_value' => $value]);
        }
        return $this->insert(['setting_key' => $key, 'setting_value' => $value]);
    }
}
