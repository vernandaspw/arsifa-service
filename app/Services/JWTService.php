<?php

namespace App\Services;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class JWTService
{
    public static function jwtAlgo()
    {
        return 'HS256';
    }
    public static function jwtSecret()
    {
        return env('JWT_SECRET');
    }
    public static function jwtSecretRefresh()
    {
        return env('JWT_SECRET_REFRESH');
    }

    public static function createToken($user_id)
    {
        $payload = [
            'sub' => $user_id, // Subject
            'iat' => time(), // Issued at
            //    'exp' => time() + 3600 // Expiration time (1 hour)
            'exp' => time() + 86400, // Expiration time (1 day)
        ];

        return JWT::encode($payload, JWTService::jwtSecret(), JWTService::jwtAlgo());
    }

    public static function verifyToken($token)
    {
        try {
            return response()->json([
                'msg' => 'success',
                'data' => JWT::decode($token, new Key(JWTService::jwtSecret(), JWTService::jwtAlgo()))
            ]);
        } catch (ExpiredException $e) {
            return response()->json(['msg' => 'Token has expired'], 403);
        } catch (SignatureInvalidException $e) {
            return response()->json(['msg' => 'Invalid token signature'], 401);
        } catch (BeforeValidException $e) {
            return response()->json(['msg' => 'Token is not yet valid'], 401);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Invalid token'], 401);
        }
    }

    public static function createTokenRefresh($user_id)
    {
        $payload = [
            'sub' => $user_id, // Subject
            'iat' => time(), // Issued at
            //    'exp' => time() + 3600 // Expiration time (1 hour)
            'exp' => time() + 86400, // Expiration time (1 day)
        ];

        return JWT::encode($payload, JWTService::jwtSecretRefresh(), JWTService::jwtAlgo());
    }

    public static function verifyTokenRefresh($token)
    {
        try {
            return response()->json([
                'msg' => 'success',
                'data' => JWT::decode($token, new Key(JWTService::jwtSecretRefresh(), JWTService::jwtAlgo())),
            ]);
        } catch (ExpiredException $e) {
            return response()->json(['msg' => 'Refresh token has expired'], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json(['msg' => 'Invalid refresh token signature'], 401);
        } catch (BeforeValidException $e) {
            return response()->json(['msg' => 'Refresh token is not yet valid'], 401);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Invalid refresh token'], 401);
        }
    }
}
