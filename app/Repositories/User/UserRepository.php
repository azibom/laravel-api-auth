<?php

namespace App\Repositories\User;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;
use GuzzleHttp\Exception\ClientException;
use App\Repositories\User\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {
    const SUCCUSUS_STATUS_CODE = 200;
    const UNAUTHORISED_STATUS_CODE = 401;
    const BASE_URL = "http://mylemp-nginx";

    public function __construct(Client $client) {
        $this->http = $client;
    }

    public function register(Request $request) {
        $email = $request->email;
        $password = $request->password;
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        User::create($input);
        $response = $this->getTokenAndRefreshToken($email, $password);
        return $this->response($response["data"], $response["statusCode"]);
    }

    public function login(Request $request) {
        $email = $request->email;
        $password = $request->password;

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $response = $this->getTokenAndRefreshToken($email, $password);
            $data = $response["data"];
            $statusCode =  $response["statusCode"];
        } else {
            $data = ['error'=>'Unauthorised'];
            $statusCode =  self::UNAUTHORISED_STATUS_CODE;
        }

        return $this->response($data, $statusCode);
    }

    public function refreshToken(Request $request) {
        if (is_null($request->header('Refreshtoken'))) {
            return $this->response(['error'=>'Unauthorised'], self::UNAUTHORISED_STATUS_CODE);
        }

        $refresh_token = $request->header('Refreshtoken');
        $Oclient = $this->getOClient();
        $formParams = [ 'grant_type' => 'refresh_token',
                        'refresh_token' => $refresh_token,
                        'client_id' => $Oclient->id,
                        'client_secret' => $Oclient->secret,
                        'scope' => '*'];

        return $this->sendRequest("/oauth/token", $formParams);
    }

    public function details() {
        $user = Auth::user();
        return $this->response($user, self::SUCCUSUS_STATUS_CODE);
    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return $this->response(['message' => 'Successfully logged out'], self::SUCCUSUS_STATUS_CODE);
    }

    public function response($data, int $statusCode) {
        $response = ["data"=>$data, "statusCode"=>$statusCode];
        return $response;
    }

    public function getTokenAndRefreshToken(string $email, string $password) {
        $Oclient = $this->getOClient();
        $formParams = [ 'grant_type' => 'password',
                        'client_id' => $Oclient->id,
                        'client_secret' => $Oclient->secret,
                        'username' => $email,
                        'password' => $password,
                        'scope' => '*'];

        return $this->sendRequest("/oauth/token", $formParams);
    }

    public function sendRequest(string $route, array $formParams) {
        try {
            $url = self::BASE_URL.$route;
            $response = $this->http->request('POST', $url, ['form_params' => $formParams]);

            $statusCode = self::SUCCUSUS_STATUS_CODE;
            $data = json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            echo $e->getMessage();
            $statusCode = $e->getCode();
            $data = ['error'=>'OAuth client error'];
        }

        return ["data" => $data, "statusCode"=>$statusCode];
    }

    public function getOClient() {
        return OClient::where('password_client', 1)->first();
    }
}
