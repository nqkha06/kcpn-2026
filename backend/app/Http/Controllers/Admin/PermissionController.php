<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $permissions = Permission::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return Inertia::render('Admin/permissions/list', [
            'permissions' => $permissions->items(),
            'pagination' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
                'from' => $permissions->firstItem(),
                'to' => $permissions->lastItem(),
            ],
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/permissions/add');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        return Inertia::render('Admin/permissions/edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,'.$permission->id],
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
