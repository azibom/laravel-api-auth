<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Repositories\User\UserRepositoryInterface;

class UserController extends Controller {
    const SUCCUSUS_STATUS_CODE = 200;
    const UNAUTHORISED_STATUS_CODE = 401;

    public function __construct(UserRepositoryInterface $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function login(UserLoginRequest $request) {
        $response = $this->userRepository->login($request);
        return response()->json($response["data"], $response["statusCode"]);
    }

    public function register(UserRegisterRequest $request) {
        $response = $this->userRepository->register($request);
        return response()->json($response["data"], $response["statusCode"]);
    }

    public function details() {
        $response = $this->userRepository->details();
        return response()->json($response["data"], $response["statusCode"]);
    }

    public function logout(Request $request) {
        $response = $this->userRepository->logout($request);
        return response()->json($response["data"], $response["statusCode"]);
    }

    public function refreshToken(Request $request) {
        $response = $this->userRepository->refreshToken($request);
        return response()->json($response["data"], $response["statusCode"]);
    }
}
