<?php

namespace App\Controllers;

use App\Models\UserModel;

class Settings extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Settings',
            'user' => $this->getLoggedInUser(),
            'userData' => $this->getUserData()
        ];

        return view('settings/index', $data);
    }

    private function getUserData()
    {
        $session = session();
        $userModel = new UserModel();
        return $userModel->find($session->get('id'));
    }

    public function updateProfile()
    {
        $session = session();
        $userModel = new UserModel();
        $userId = $session->get('id');

        $fullName = $this->request->getPost('full_name');
        $email = $this->request->getPost('email');

        if (empty($fullName)) {
            return redirect()->back()->with('error', 'Full name cannot be empty.');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Please enter a valid email address.');
        }

        // Check if email is already taken by another user
        $existingUser = $userModel->where('email', $email)
                                   ->where('id !=', $userId)
                                   ->first();
        
        if ($existingUser) {
            return redirect()->back()->with('error', 'Email address is already taken.');
        }

        $data = [
            'full_name' => $fullName,
            'email' => $email
        ];
        
        // Skip validation for profile update (no password required)
        $userModel->skipValidation(true);
        
        if ($userModel->update($userId, $data)) {
            $session->set('full_name', $fullName);
            $session->set('email', $email);
            return redirect()->to('/settings')->with('success', 'Profile updated successfully.');
        } else {
            $errors = $userModel->errors();
            $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Failed to update profile.';
            return redirect()->back()->with('error', $errorMsg);
        }
    }

    public function uploadPhoto()
    {
        $session = session();
        $userModel = new UserModel();
        $userId = $session->get('id');

        $file = $this->request->getFile('profile_photo');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Please select a valid image file.');
        }

        // Validate file type
        $validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $validTypes)) {
            return redirect()->back()->with('error', 'Only JPG, PNG, and GIF images are allowed.');
        }

        // Validate file size (max 2MB)
        if ($file->getSize() > 2048000) {
            return redirect()->back()->with('error', 'Image size must be less than 2MB.');
        }

        // Delete old photo if exists
        $user = $userModel->find($userId);
        if (!empty($user['profile_photo']) && file_exists(FCPATH . 'uploads/profiles/' . $user['profile_photo'])) {
            unlink(FCPATH . 'uploads/profiles/' . $user['profile_photo']);
        }

        // Create uploads directory if not exists
        $uploadPath = FCPATH . 'uploads/profiles/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $newName = 'profile_' . $userId . '_' . time() . '.' . $file->getExtension();
        
        if ($file->move($uploadPath, $newName)) {
            $userModel->update($userId, ['profile_photo' => $newName]);
            return redirect()->to('/settings')->with('success', 'Profile photo updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to upload photo.');
        }
    }

    public function resetPhoto()
    {
        $session = session();
        $userModel = new UserModel();
        $userId = $session->get('id');

        $user = $userModel->find($userId);
        
        // Delete photo file if exists
        if (!empty($user['profile_photo']) && file_exists(FCPATH . 'uploads/profiles/' . $user['profile_photo'])) {
            unlink(FCPATH . 'uploads/profiles/' . $user['profile_photo']);
        }

        $userModel->update($userId, ['profile_photo' => null]);
        
        return redirect()->to('/settings')->with('success', 'Profile photo reset to default.');
    }

    public function changePassword()
    {
        $session = session();
        $userModel = new UserModel();
        $userId = $session->get('id');

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'All password fields are required.');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'New password and confirmation do not match.');
        }

        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'New password must be at least 6 characters long.');
        }

        // Verify current password
        $user = $userModel->find($userId);
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        // Update password - skip validation and let the model handle hashing
        $userModel->skipValidation(true);
        
        if ($userModel->update($userId, ['password' => $newPassword])) {
            return redirect()->to('/settings')->with('success', 'Password changed successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to change password.');
        }
    }
}
