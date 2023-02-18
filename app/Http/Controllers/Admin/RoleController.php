<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $role, $permission, $rolePermission;

    public function __construct()
    {
        $this->role = new Role();
        $this->rolePermission = new RolePermission();
        $this->permission = new Permission();
    }

    public function listing()
    {
        $roles = $this->role->newQuery()->where('id', '!=', 1)->get();
        $permission = $this->permission->newQuery()->get();
        return view('admin.roles.listing', compact('roles', 'permission'));
    }
}
