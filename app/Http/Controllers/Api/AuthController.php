<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserToken;
use App\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function daftar(Request $req)
    {
        try {
            $nama = $req->nama;
            $username = $req->username;
            $email = $req->email;
            $password = $req->password;

            if (!$nama) {
                return response()->json(['errors' =>
                    [
                        [
                            'path' => 'nama',
                            'msg' => 'nama wajib di isi',
                        ],
                    ],
                ], 422);
            }
            if (!$email) {
                return response()->json(['errors' =>
                    [
                        [
                            'path' => 'email',
                            'msg' => 'email wajib di isi',
                        ],
                    ],
                ], 422);
            }
            if (!$username) {
                return response()->json(['errors' =>
                    [
                        [
                            'path' => 'username',
                            'msg' => 'username wajib di isi',
                        ],
                    ],
                ], 422);
            }
            if (!$password) {
                return response()->json(['errors' =>
                    [
                        [
                            'path' => 'password',
                            'msg' => 'password wajib di isi',
                        ],
                    ],
                ], 422);
            }

            // create token
            return response()->json([
                'msg' => 'berhasil daftar silahkan login',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
            ], 500);
        }
    }

    public function login(Request $req)
    {
        try {
            $username = $req->username;
            $email = $req->email;
            $password = $req->password;

            if (empty($username) && empty($email)) {
                return response()->json(['errors' =>
                    [
                        [
                            'path' => 'usernameOrEmail',
                            'msg' => 'username atau email wajib di isi',
                        ],
                    ],
                ], 422);
            }

            if (!$password) {
                return response()->json(['errors' =>
                    [
                        [
                            'path' => 'password',
                            'msg' => 'password wajib di isi',
                        ],
                    ],
                ], 422);
            }

            if ($username) {
                $cekUsername = User::where('username', $username)->first();
                if (!$cekUsername) {
                    return response()->json(['msg' => 'Username tidak ditemukan'], 400);
                }
                $user = $cekUsername;
            }

            if ($email) {
                $cekEmail = User::where('email', $email)->first();
                if (!$cekEmail) {
                    return response()->json(['msg' => 'Email tidak ditemukan'], 400);
                }
                $user = $cekEmail;
            }

            if (!$user) {
                return response()->json(['msg' => 'Akun tidak ditemukan'], 400);
            }

            if ($user->isActive == false) {
                return response()->json(['msg' => 'Akun tidak aktif'], 400);
            }

            if ($user->password_failed >= 10) {
                $user->update([
                    'isLock' => true,
                ]);
                return response()->json(['msg' => 'Akun terkunci, silahkan hubungi admin IT'], 400);
            }
            if ($user->isLock == true) {
                return response()->json(['msg' => 'Akun terkunci, silahkan hubungi admin IT'], 400);
            }

            if (!Hash::check($password, $user->password)) {
                $user->update([
                    'password_failed' => $user->password_failed + 1,
                ]);
                if ($user->password_failed >= 10) {
                    $user->update([
                        'isLock' => true,
                    ]);
                }
                return response()->json(['msg' => 'password salah ' . $user->password_failed . ' kali'], 400);
            }

            $token = JWTService::createToken($user->id);
            $tokenRefresh = JWTService::createTokenRefresh($user->id);

            $device = $req->header('User-Agent');

            $userToken = UserToken::where('user_id', $user->id)->where('device', $device)->first();
            if ($userToken) {
                $userToken->token = $token;
                $userToken->tokenRefresh = $tokenRefresh;
                $userToken->save();
            } else {
                UserToken::create([
                    'user_id' => $user->id,
                    'device' => $device,
                    'token' => $token,
                    'tokenRefresh' => $tokenRefresh,
                ]);
            }
            $user->update([
                'password_failed' => 0,
            ]);
            // create token
            return response()->json([
                'token' => $token,
                'token_refresh' => $tokenRefresh,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
            ], 500);
        }
    }

    public function me(Request $req)
    {
        $user = $req->attributes->get('user');
        $user = User::with('apps')->find($user->sub);

        // tampilkan aplikasi -> filtur dan role


        return response()->json($user, 200);
    }

    public function tokenRefresh(Request $req)
    {
        try {
            $input_tokenRefresh = $req->tokenRefresh;
            $decodedToken = JWTService::verifyTokenRefresh($input_tokenRefresh);
            if ($decodedToken->getStatusCode() != 200) {
                return $decodedToken;
            }
            $response = json_decode($decodedToken->getContent());
            $user_id = $response->data->sub;

            $userToken = UserToken::where('tokenRefresh', $input_tokenRefresh)->first();
            if (!$userToken) {
                return response()->json([
                    'msg' => 'refreshToken invalid',
                ], 401);
            }

            $token = JWTService::createToken($user_id);
            $tokenRefresh = JWTService::createTokenRefresh($user_id);

            $userToken->token = $token;
            $userToken->tokenRefresh = $tokenRefresh;
            $userToken->save();

            return response()->json([
                'token' => $token,
                'token_refresh' => $tokenRefresh,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
            ], 500);
        }
    }

    public function logout(Request $req)
    {
        // delete token device saat ini
        try {
            $token = $req->bearerToken();
            $userToken = UserToken::where('token', $token)->first();
            $userToken->delete();

            return response()->json([
                'msg' => 'success',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    public function logoutDevice($userTokenId)
    {
        try {
            $userToken = UserToken::where('id', $userTokenId)->first();
            $userToken->delete();

            return response()->json([
                'msg' => 'success',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    public function logoutAllDevice(Request $req)
    {

        try{
            $user = User::me($req);

            $userTokens = UserToken::where('user_id', $user->id)->get();
            foreach ($userTokens as $userToken) {
                $userToken->delete();
            }

            return response()->json([
                'msg' => 'success',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

}
