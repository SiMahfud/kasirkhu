<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
// use CodeIgniter\Shield\Models\GroupModel; // Not used in this simplified version

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $userModel = model(ShieldUserModel::class);

        // Check if admin user already exists by username
        $admin = $userModel->where('username', 'admin')->first();

        if (!$admin) {
            log_message('debug', '[AdminUserSeeder] Admin user "admin" not found, attempting to create.');
            $adminUserEntity = new ShieldUser([
                'username' => 'admin',
                'email'    => 'admin@example.com',
                'password' => 'password123', // Will be hashed by Shield's UserModel
                'role'     => 'admin',
                'name'     => 'Administrator',
            ]);

            if ($userModel->save($adminUserEntity)) {
                log_message('debug', '[AdminUserSeeder] Admin user "admin" CREATED successfully.');

                // Optionally, add to group and assign permissions if needed for basic tests
                // For now, just creating the user to see if "no such column: name" is resolved.
                $savedAdmin = $userModel->findById($userModel->getInsertID());
                if ($savedAdmin) {
                    // Ensure 'admin' group exists or create it
                    $groupModel = model(\CodeIgniter\Shield\Models\GroupModel::class);
                    $adminGroup = $groupModel->where('name', 'admin')->first();
                    if (!$adminGroup) {
                        $groupModel->insert([
                            'name'        => 'admin',
                            'description' => 'Administrator group',
                        ]);
                        $adminGroup = $groupModel->where('name', 'admin')->first(); // Re-fetch
                    }
                    if ($adminGroup) {
                        $savedAdmin->addGroup('admin');
                        log_message('debug', '[AdminUserSeeder] Added user "admin" to group "admin".');

                        // Basic permissions for testing controllers
                        $authorize = service('authorization');
                        $permissions = ['admin.access', 'admin.settings', 'admin.users.list', 'admin.users.create', 'admin.users.edit', 'admin.users.delete'];
                        foreach($permissions as $perm) {
                            if (!$authorize->permission($perm)) {
                                $authorize->createPermission($perm, 'Permission for ' . $perm);
                            }
                            $authorize->addPermissionToGroup($perm, $adminGroup->id);
                        }
                        log_message('debug', '[AdminUserSeeder] Assigned basic permissions to admin group.');
                    }
                }

            } else {
                log_message('error', '[AdminUserSeeder] FAILED to create admin user "admin". Errors: ' . json_encode($userModel->errors()));
            }
        } else {
            log_message('debug', '[AdminUserSeeder] Admin user "admin" already exists.');
            // Ensure existing admin is in admin group and has permissions
            if (!$admin->inGroup('admin')) {
                $admin->addGroup('admin');
                log_message('debug', '[AdminUserSeeder] Existing admin user "admin" added to admin group.');
            }
             $authorize = service('authorization');
             $adminGroup = model(\CodeIgniter\Shield\Models\GroupModel::class)->where('name', 'admin')->first();
             if ($adminGroup) {
                $permissions = ['admin.access', 'admin.settings', 'admin.users.list', 'admin.users.create', 'admin.users.edit', 'admin.users.delete'];
                foreach($permissions as $perm) {
                    if (!$authorize->permission($perm)) {
                         $authorize->createPermission($perm, 'Permission for ' . $perm);
                    }
                    if (!$authorize->doesUserHavePermission($admin->id, $perm)) {
                        $authorize->addPermissionToGroup($perm, $adminGroup->id); // Re-assign to group to be sure
                    }
                }
                log_message('debug', '[AdminUserSeeder] Checked/Re-assigned basic permissions to admin group for existing admin.');
             }
        }
    }
}
