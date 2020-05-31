<?php

namespace App\Repositories\User;

use Illuminate\Http\Request;

interface UserRepositoryInterface {
    public function register(Request $request);
    public function login(Request $request);
    public function refreshToken(Request $request);
    public function details();
    public function logout(Request $request);
    public function response($data, int $statusCode);
    public function getTokenAndRefreshToken(string $email, string $password);
    public function sendRequest(string $route, array $formParams);
    public function getOClient();
}