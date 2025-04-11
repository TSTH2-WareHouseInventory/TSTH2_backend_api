<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as ModelsRole;

class PermissionController extends Controller
{
    public function togglePermission(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
            'permission' => 'required|string',
            'status' => 'required|boolean'
        ]);

        $role = ModelsRole::where('name', $request->role)->firstOrFail();
        $permission = Permission::where('name', $request->permission)->firstOrFail();

        if ($request->status) {
            // aktifkan permission
            if ($role->hasPermissionTo($permission)) {
                return response()->json(['message' => "Permission {$request->permission} sudah dimiliki oleh {$request->role}"], 409);
            }
            $role->givePermissionTo($permission);
            return response()->json(['message' => "Permission {$request->permission} diberikan ke {$request->role}"]);
        } else {
            //ini mematikan permission
            if (!$role->hasPermissionTo($permission)) {
                return response()->json(['message' => "Permission {$request->permission} sudah dicabut sebelumnya dari {$request->role}"], 409);
            }
            $role->revokePermissionTo($permission);
            return response()->json(['message' => "Permission {$request->permission} dicabut dari {$request->role}"]);
        }
    }
    public function index()
    {
        $permissions = Permission::all();
        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);
    }
}
