<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Exception;

class TestController extends Controller
{
    
    public function handlePasswordTool(Request $request)
    {
        
        $request->validate([
            'text'   => 'required|string',
            'option' => 'required|in:1,2',
        ]);

        $text   = $request->input('text');
        $option = $request->input('option');

        // Option 1: Plain password to Encrypted string
        if ($option == 1) {
            $encryptedValue = Crypt::encryptString($text);

            return response()->json([
                'status'           => 'success',
                'action'           => 'Encrypted (Option 1)',
                'input_password'   => $text,
                'encrypted_output' => $encryptedValue
            ]);
        }

        
        if ($option == 2) {
            try {
                $decryptedValue = Crypt::decryptString($text);

                return response()->json([
                    'status'            => 'success',
                    'action'            => 'Decrypted (Option 2)',
                    'encrypted_input'   => $text,
                    'original_password' => $decryptedValue
                ]);
            } catch (DecryptException $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid encrypted string provided.'
                ], 400);
            }
        }
    }

    public function updateTenantPasswordDynamic(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string',
            'app_name'     => 'required|string',
        ]);

        $newPassword = $request->input('new_password');
        $appName     = $request->input('app_name');

        try {
            $deployment = DB::table('deployment')->where('app_name', $appName)->first();
            
            if (!$deployment) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'App configuration not found in deployment table.'
                ], 404);
            }

            $dbUser = $deployment->db_username; 

            DB::statement("ALTER USER '{$dbUser}'@'localhost' IDENTIFIED BY '{$newPassword}'");
            
            DB::statement("FLUSH PRIVILEGES");

           
            $encryptedPassword = Crypt::encryptString($newPassword);

            
            DB::table('deployment')
                ->where('app_name', $appName)
                ->update(['db_password' => $encryptedPassword]); 
            return response()->json([
                'status'           => 'success',
                'message'          => 'Password updated automatically in MySQL and securely encrypted in deployment table!',
                'username'         => $dbUser,
                'encrypted_output' => $encryptedPassword
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update password dynamically: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function testDynamicConnection(Request $request)
    {
        $appName = $request->input('app_name', 'v_app_1');

        try {
            $deployment = DB::table('deployment')->where('app_name', $appName)->first();

            if (!$deployment) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Registration not complete. App configuration not found.'
                ], 404);
            }

            $decryptedPassword = Crypt::decryptString($deployment->db_password);


            Config::set('database.connections.dynamic_tenant', [
                'driver'    => 'mysql',
                'host'      => $deployment->db_host,
                'port'      => $deployment->db_port,
                'database'  => $deployment->db_database,
                'username'  => $deployment->db_username,
                'password'  => $decryptedPassword,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
            ]);

            
            DB::purge('dynamic_tenant');
              DB::reconnect('dynamic_tenant');

            $sampleData = DB::connection('dynamic_tenant')->table('users')->limit(5)->get();

            return response()->json([
                'status'             => 'success',
                'message'            => 'Database connected successfully using encrypted password!',
                'connected_database' => $deployment->db_database,
                'db_user'            => $deployment->db_username,
                'db_password'        => $decryptedPassword,
                'users_table_data'  => $sampleData
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Database Connection Failed: ' . $e->getMessage()
            ], 500);
        }
    }
}