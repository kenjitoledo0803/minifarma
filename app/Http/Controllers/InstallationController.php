<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InstallationController extends Controller
{
    public function setupDatabase()
    {
        // Security check: only allow if app is local OR via a specific secret key if needed.
        // For this MVP, we'll allow it but warn the user.
        
        try {
            // 1. Check connection
            DB::connection()->getPdo();
            
            // 2. Run Migrations
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            // 3. Seed Basic Data (Admin User, Default Branch)
            // We can do this programmatically here without a Seeder class to keep it self-contained.
            
            $log = [];
            
            // Create Default Branch if not exists
            if (!DB::table('branches')->where('name', 'Sucursal Central')->exists()) {
                $branchId = DB::table('branches')->insertGetId([
                    'name' => 'Sucursal Central',
                    'address' => 'Dirección Pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $log[] = "Sucursal 'Sucursal Central' creada.";
            } else {
                $branchId = DB::table('branches')->where('name', 'Sucursal Central')->value('id');
            }

            // Create Admin User if not exists
            if (!DB::table('users')->where('email', 'admin@minifarmacia.com')->exists()) {
                DB::table('users')->insert([
                    'name' => 'Administrador',
                    'email' => 'admin@minifarmacia.com',
                    'password' => bcrypt('admin123'), // Default password
                    'branch_id' => $branchId,
                    'role' => 'super_admin',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $log[] = "Usuario Admin creado (admin@minifarmacia.com / admin123).";
            }

            return response()->json([
                'status' => 'success',
                'message' => '¡Sistema Instalado Correctamente!',
                'migration_output' => $output,
                'setup_log' => $log,
                'instructions' => 'Por favor, borra la ruta /install-system de routes/web.php ahora por seguridad.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al instalar: ' . $e->getMessage(),
                'hint' => 'Verifica tus credenciales de Base de Datos en el archivo .env',
            ], 500);
        }
    }
}
