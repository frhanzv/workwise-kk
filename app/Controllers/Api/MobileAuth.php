<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;
use App\Models\UserModel;

class MobileAuth extends BaseController
{
    public function login()
    {
        $username = $this->request->getPost('username') ?: $this->request->getJSON(true)['username'] ?? null;
        $password = $this->request->getPost('password') ?: $this->request->getJSON(true)['password'] ?? null;

        if (!$username || !$password) {
            return $this->jsonError('Username and password are required.', 422);
        }

        $user = (new UserModel())
            ->groupStart()
            ->where('username', $username)
            ->orWhere('email', $username)
            ->groupEnd()
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->jsonError('Invalid credentials.', 401);
        }

        if ((int) $user['is_active'] !== 1) {
            return $this->jsonError('Account is deactivated.', 403);
        }

        $token = (new ApiTokenModel())->createForUser((int) $user['id']);

        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'        => (int) $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'email'     => $user['email'],
            ],
        ]);
    }

    public function logout()
    {
        $token = $this->extractToken();
        if ($token) {
            (new ApiTokenModel())->where('token', $token)->delete();
        }
        return $this->response->setJSON(['success' => true, 'message' => 'Logged out.']);
    }

    private function extractToken(): ?string
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function jsonError(string $message, int $code = 400)
    {
        return $this->response->setJSON(['success' => false, 'message' => $message])->setStatusCode($code);
    }
}
