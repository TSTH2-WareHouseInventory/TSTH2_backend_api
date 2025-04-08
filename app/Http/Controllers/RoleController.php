<?php
namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_role', only: ['index', 'show']),
            new Middleware('permission:create_role', only: ['store']),
            new Middleware('permission:update_role', only: ['update']),
            new Middleware('permission:delete_role', only: ['destroy']),
        ];
    }

    public function index()
    {
        $roles = $this->roleService->getAll();
        return RoleResource::collection($roles);
    }

    public function store(Request $request)
    {
        try {
            $role = $this->roleService->create($request->all());
            return response()->json([
                'message' => 'Role berhasil dibuat',
                'data' => new RoleResource($role)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        }
    }

    public function show($id)
    {
        $role = $this->roleService->getById($id);
        return $role ? new RoleResource($role) : response()->json(['message' => 'Role tidak ditemukan.'], 404);
    }

    public function update(Request $request, $id)
    {
        try {
            $updated = $this->roleService->update($id, $request->all());
            return $updated
                ? response()->json(['message' => 'Role berhasil diperbarui.', 'data' => new RoleResource($updated)])
                : response()->json(['message' => 'Role tidak ditemukan.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        }
    }

    public function destroy($id)
    {
        return $this->roleService->delete($id)
            ? response()->json(['message' => 'Role berhasil dihapus.'])
            : response()->json(['message' => 'Role tidak ditemukan.'], 404);
    }
}
