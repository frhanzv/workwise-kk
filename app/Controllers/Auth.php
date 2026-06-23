<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $session = session();
        $userModel = new UserModel();
        
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        
        $user = $userModel->where('username', $username)
                          ->orWhere('email', $username)
                          ->first();
        
        if ($user) {
            $pass_verify = password_verify($password, $user['password']);
            
            if ($pass_verify) {
                if ($user['is_active'] == 1) {
                    $session_data = [
                        'id'         => $user['id'],
                        'username'   => $user['username'],
                        'email'      => $user['email'],
                        'full_name'  => $user['full_name'],
                        'isLoggedIn' => true
                    ];
                    
                    $session->set($session_data);
                    return redirect()->to('/dashboard');
                } else {
                    $session->setFlashdata('error', 'Your account has been deactivated. Please contact the administrator.');
                    return redirect()->to('/login');
                }
            } else {
                $session->setFlashdata('error', 'Invalid username or password.');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('error', 'Invalid username or password.');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }
}
