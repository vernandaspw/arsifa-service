<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserApp;
use App\Models\UserToken;
use App\Services\JWTService;
use Closure;
use Illuminate\Http\Request;

class JWTAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $appid = $request->header('appid');
        $device = $request->header('User-Agent');

        if (!$token) {
            return response()->json(['msg' => 'Unauthorized'], 401);
        }

        $decodedToken = JWTService::verifyToken($token);

        if ($decodedToken->getStatusCode() != 200) {
            return $decodedToken;
        }
        $response = json_decode($decodedToken->getContent());
        $user = User::find($response->data->sub);
        // cek kondisi user
        if ($user->isLock == true) {
            return response()->json(['msg' => 'Akun terkunci, silahkan hubungi admin IT'], 401);
        }
        if ($user->isActive == false) {
            return response()->json(['msg' => 'Akun tidak aktif'], 401);
        }

        $user_apps = UserApp::where('user_id', $user->id)->where('app_id', $appid)->first();
        if (!$user_apps) {
            return response()->json([
                'msg' => 'tidak memiliki akses pada aplikasi tersebut',
            ], 400);
        }

        $userToken = UserToken::where('token', $token)->first();
        if (!$userToken) {
            return response()->json(['msg' => 'Token telah expired'], 401);
        }

        $request->attributes->set('user', $response->data);
        return $next($request);
    }
}
