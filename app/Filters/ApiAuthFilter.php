<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token      = null;

        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            $token = $request->getGet('token') ?: $request->getPost('token');
        }

        if (!$token) {
            return service('response')->setJSON([
                'success' => false,
                'message' => 'API token required. Use Authorization: Bearer {token}',
            ])->setStatusCode(401);
        }

        $apiToken = (new \App\Models\ApiTokenModel())->findValid($token);
        if (!$apiToken) {
            return service('response')->setJSON([
                'success' => false,
                'message' => 'Invalid or expired API token.',
            ])->setStatusCode(401);
        }

        \App\Libraries\ApiContext::$userId = (int) $apiToken['user_id'];
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
