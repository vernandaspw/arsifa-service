<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Simpeg\SimpegUser;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public static function syncUserSimpeg()
    {
        try {
            $simpegUsers = SimpegUser::get();

            // buat perbandingan data apakah memiliki data terbaru
            // matikan aktifkan filtur filed password, jika telah ada filtur ubah password

            $store_users = [];
            $update_users = [];
            foreach ($simpegUsers as $simpegUser) {

                $simpegUserArray = [
                    'username' => $simpegUser->username,
                    'password' => $simpegUser->password,
                    'nama' => $simpegUser->nama,
                    'isActive' => $simpegUser->status == 'Aktif' ? 1 : 0,
                ];

                $user = User::where('username', $simpegUser->username)->first();

                if (isset($user) == false) {
                    $store_users[] = $simpegUserArray;
                } else {
                    // cek perubahan password, nama, status
                    // jika tidak sama, perbarui
                    if (
                        $user->nama != $simpegUserArray['nama'] ||
                        $user->password != $simpegUserArray['password'] ||
                        $user->isActive != $simpegUserArray['isActive']
                    ) {
                        $update_users[] = $simpegUserArray;
                    }
                }
            }
            $dataStoreUsers = array_chunk($store_users, 25);
            $dataStoredCount = 0;
            if ($dataStoreUsers) {
                foreach ($dataStoreUsers as $dataStoreUser) {
                    foreach ($dataStoreUser as $item) {
                        $stored = new User();
                        $stored->nama = $item['nama'];
                        $stored->username = $item['username'];
                        $stored->password = $item['password'];
                        $stored->isActive = $item['isActive'];
                        $stored->save();

                        $dataStoredCount += 1;
                    }
                }
            }

            $dataUpdateUsers = array_chunk($update_users, 25);
            $dataUpdatedCount = 0;
            if ($dataUpdateUsers) {
                foreach ($dataUpdateUsers as $dataUpdateUser) {
                    foreach ($dataUpdateUser as $item) {
                        $Updated = User::where('username', $item['username'])->first();
                        $Updated->nama = $item['nama'];
                        $Updated->password = $item['password'];
                        $Updated->isActive = $item['isActive'];
                        $Updated->save();

                        $dataUpdatedCount += 1;
                    }
                }
            }

            return response()->json([
                'msg' => 'success',
                'data' => [
                    'dataStoredCount' => $dataStoredCount,
                    'dataUpdatedCount' => $dataUpdatedCount,
                ],
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function unlock(Request $req)
    {
        $email = $req->email;
        try {
            if (!$email) {
                return response()->json(['msg' => 'email wajib di isi'], 400);
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json(['msg' => 'email tidak ditemukan'], 400);
            }
            $user->update([
                'password_failed' => 0,
                'isLock' => false,
            ]);

            return response()->json([
                'msg' => 'success unlock',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
