<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        try {

            $username = strtolower($request->username);

            $connection = Container::getDefaultConnection();

            // 🔐 LDAP AUTHENTICATION (TRY COMMON AD FORMATS)
            $loginSuccess = $connection->auth()->attempt(
                $username . '@evrmc.local',   // most common AD format
                $request->password,
                $stayBound = true
            );

            if (! $loginSuccess) {
                return response()->json([
                    'message' => 'Invalid LDAP credentials'
                ], 401);
            }

            // 🔍 GET USER FROM AD (FIXED QUERY)
            $ldapUser = LdapUser::query()
                ->whereEquals('samaccountname', $username)
                ->first();

            // 🔁 fallback (important for real AD environments)
            if (! $ldapUser) {
                $ldapUser = LdapUser::query()
                    ->whereEquals('cn', $request->username)
                    ->orWhereEquals('displayname', $request->username)
                    ->first();
            }

            if (! $ldapUser) {
                return response()->json([
                    'message' => 'User authenticated but not found in directory lookup'
                ], 404);
            }

            // 🏥 SYNC TO LOCAL DATABASE
            $user = User::updateOrCreate(
                [
                    'email' => $ldapUser->mail ?? $username . '@evrmc.local'
                ],
                [
                    'name' => $ldapUser->cn[0] ?? $request->username,
                    'password' => bcrypt(str()->random(32)),
                ]
            );

            // 🔐 SANCTUM TOKEN
            // 🔥 FORCE SINGLE DEVICE LOGIN
            $user->tokens()->delete();
            
           $plainTextToken = $user->createToken('his-ldap-token')->plainTextToken;

            $currentToken = $user->tokens()->latest('id')->first();

            $user->update([
                'last_login_at'    => now(),
                'current_token_id' => $currentToken->id,
            ]);

            return response()->json([
                'user'  => $user->fresh(),
                'token' => $plainTextToken,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'LDAP authentication error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}