<?php

namespace App\Controllers;

// Use Shield's UserModel and User entity
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Models\GroupModel;

class UserController extends BaseController
{
    protected ShieldUserModel $userModel; // Type hint to Shield's UserModel
    protected GroupModel $groupModel;
    protected $helpers = ['form', 'url', 'auth']; // Added 'auth' helper for logged_in() etc. if needed by layout

    // Define group names as constants for consistency
    private const GROUP_ADMIN = 'admin';
    private const GROUP_CASHIER = 'cashier';


    public function __construct()
    {
        $this->userModel = model(ShieldUserModel::class);
        $this->groupModel = model(GroupModel::class);
    }

    /**
     * Display a list of users with pagination.
     */
    public function index()
    {
        if (!auth()->user() || !auth()->user()->can('admin.users.list')) {
            return redirect()->to(base_url())->with('error', 'You do not have permission to access this page.');
        }

        $data = [
            'users' => $this->userModel->orderBy('username', 'asc')->paginate(10),
            'pager' => $this->userModel->pager,
            'title' => 'User Management',
        ];

        return view('users/index', $data);
    }

    /**
     * Show the form for creating a new user.
     */
    public function new()
    {
        if (!auth()->user() || !auth()->user()->can('admin.users.create')) {
            return redirect()->to(base_url('admin/users'))->with('error', 'You do not have permission to perform this action.');
        }
        $data = [
            'title' => 'Add New User',
            // 'roles' => [self::GROUP_ADMIN, self::GROUP_CASHIER] // old
            'available_groups' => [self::GROUP_ADMIN, self::GROUP_CASHIER] // new, to match edit view
        ];
        return view('users/new', $data);
    }

    /**
     * Process the creation of a new user.
     */
    public function create()
    {
        if (!auth()->user() || !auth()->user()->can('admin.users.create')) {
            return redirect()->to(base_url('admin/users'))->with('error', 'You do not have permission to perform this action.');
        }

        $rules = [
            // Shield's UserModel expects 'email' and handles password hashing internally
            'name' => 'required|string|max_length[255]', // Keep name if you want it on users table, or handle via UserIdentity
            'username' => 'required|string|max_length[100]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]', // email is an identity
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'group' => 'required|in_list[' . self::GROUP_ADMIN . ',' . self::GROUP_CASHIER . ']', // Changed 'role' to 'group'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Create user entity for Shield
        $user = new ShieldUser([
            'username' => $this->request->getPost('username'),
            // 'name' field is not directly on Shield's users table by default.
            // It's often stored in a related profile table or as an identity.
            // For simplicity here, we'll assume `name` might be a custom field if added to Shield's User entity or handled elsewhere.
            // If your Shield User entity has a `name` property, it will be set.
        ]);
        $user->setEmail($this->request->getPost('email'));
        $user->setPassword($this->request->getPost('password')); // Shield model will hash this

        if ($this->userModel->save($user)) {
            $userId = $this->userModel->getInsertID();
            $createdUser = $this->userModel->findById($userId);

            // Add to group
            $groupName = $this->request->getPost('group');
            $createdUser->addGroup($groupName);

            // Handle 'name' - if you have a custom field on identities or a profile table
            // For now, let's assume 'name' from the form is for display and might not be directly part of Shield's core user table.
            // If `name` is a custom field in `auth_identities.attributes` or similar:
            $createdUser->setIdentities([
                [
                    'type'   => 'name', // Custom identity type
                    'secret' => $this->request->getPost('name'),
                    'name'   => 'Full Name'
                ]
            ]);


            return redirect()->to('admin/users')->with('message', 'User created successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create user. Errors: ' . implode(', ', $this->userModel->errors()));
        }
    }

    /**
     * Show the form for editing an existing user.
     */
    public function edit($id = null)
    {
        if (!auth()->user() || !auth()->user()->can('admin.users.edit')) {
            return redirect()->to(base_url('admin/users'))->with('error', 'You do not have permission to perform this action.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            throw PageNotFoundException::forPageNotFound('User not found.');
        }

        // Prevent admin from editing their own role if they are the sole admin (example logic)
        // Or prevent editing superadmin if such concept exists.

        // Fetch user's groups
        $userGroups = $this->groupModel->getGroupsForUser($user->id);
        $userGroupNames = array_map(fn($group) => $group['name'], $userGroups);

        $data = [
            'user' => $user, // This is now a ShieldUser entity
            'userGroups' => $userGroupNames, // Pass current groups to the view
            'title' => 'Edit User: ' . esc($user->username),
            'available_groups' => [self::GROUP_ADMIN, self::GROUP_CASHIER] // Changed 'roles' to 'available_groups'
        ];
        return view('users/edit', $data);
    }

    /**
     * Process the update of an existing user.
     */
    public function update($id = null)
    {
        if (!auth()->user() || !auth()->user()->can('admin.users.edit')) {
            return redirect()->to(base_url('admin/users'))->with('error', 'You do not have permission to perform this action.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            throw PageNotFoundException::forPageNotFound('User not found.');
        }

        $rules = [
            'name' => 'required|string|max_length[255]', // Still for the 'name' identity
            'username' => "required|string|max_length[100]|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|is_unique[auth_identities.secret,id,{$user->getIdentity('email')->id ?? 0}]", // Check uniqueness for email identity
            'group' => 'required|in_list[' . self::GROUP_ADMIN . ',' . self::GROUP_CASHIER . ']', // Changed 'role' to 'group'
        ];

        // Password change is optional
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update basic user info (username)
        $user->username = $this->request->getPost('username');

        // Update email identity
        $user->setEmail($this->request->getPost('email'));

        // Update password if provided
        if ($this->request->getPost('password')) {
            $user->setPassword($this->request->getPost('password'));
        }

        // Handle 'name' identity
        // Find existing 'name' identity or create new one
        $nameIdentity = $user->getIdentity('name');
        if ($nameIdentity) {
            $nameIdentity->secret = $this->request->getPost('name');
            $user->setIdentities([$nameIdentity]); // This might need specific update logic if Shield doesn't auto-update identities on user save
        } else {
             $user->setIdentities([
                [
                    'type'   => 'name',
                    'secret' => $this->request->getPost('name'),
                    'name'   => 'Full Name'
                ]
            ]);
        }


        // Group management
        $newGroupName = $this->request->getPost('group');
        $currentGroups = $this->groupModel->getGroupsForUser($id);
        $currentGroupNames = array_map(fn($group) => $group['name'], $currentGroups);

        // Prevent admin from changing their own group to non-admin if they are the only admin
        $currentUser = auth()->user();
        if ($currentUser && (int)$currentUser->id === (int)$id && in_array(self::GROUP_ADMIN, $currentGroupNames) && $newGroupName !== self::GROUP_ADMIN) {
            $adminUsers = $this->userModel->whereInGroup(self::GROUP_ADMIN)->findAll();
            if (count($adminUsers) <= 1) {
                return redirect()->back()->withInput()->with('error', 'Cannot change the group of the only administrator.');
            }
        }

        if ($this->userModel->save($user)) {
            // Sync groups: remove from all current groups, then add to the new one.
            // More sophisticated logic might be needed if a user can be in multiple relevant groups.
            foreach ($currentGroupNames as $group) {
                 if (in_array($group, [self::GROUP_ADMIN, self::GROUP_CASHIER])) { // Only manage app-specific groups here
                    $user->removeGroup($group);
                 }
            }
            $user->addGroup($newGroupName);

            return redirect()->to('admin/users')->with('message', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update user. Errors: ' . implode(', ', $this->userModel->errors()));
        }
    }

    /**
     * Delete a user.
     */
    public function delete($id = null)
    {
        if (!auth()->user() || !auth()->user()->can('admin.users.delete')) {
            return redirect()->to(base_url('admin/users'))->with('error', 'You do not have permission to perform this action.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            throw PageNotFoundException::forPageNotFound('User not found.');
        }

        // Prevent admin from deleting themselves
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->id == $id) {
            return redirect()->to('admin/users')->with('error', 'You cannot delete your own account.');
        }

        // Prevent deletion of the only admin account
        if ($user->inGroup(self::GROUP_ADMIN)) {
            $adminUsers = $this->userModel->whereInGroup(self::GROUP_ADMIN)->findAll();
            if (count($adminUsers) <= 1) {
                return redirect()->to('admin/users')->with('error', 'Cannot delete the only administrator account.');
            }
        }

        // Shield's UserModel delete method also handles related identities, etc.
        if ($this->userModel->delete($id, true)) { // true for permanent delete
            return redirect()->to('admin/users')->with('message', 'User deleted successfully.');
        } else {
            return redirect()->to('admin/users')->with('error', 'Failed to delete user.');
        }
    }
}
