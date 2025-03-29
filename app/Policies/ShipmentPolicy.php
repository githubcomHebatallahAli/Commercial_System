<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShipmentPolicy
{
    use HandlesAuthorization;
    public function create(Admin $admin)
    {
        // return $admin->role_id === 1;
        return $admin->role->name === 'Super Admin';
    }

    public function edit(Admin $admin)
    {
        // return $admin->role_id === 1;
        return $admin->role->name === 'Super Admin';
    }

    public function update(Admin $admin)
    {
        // return $admin->role_id === 1;
        return $admin->role->name === 'Super Admin';
    }

    public function showAll(Admin $admin)
    {
        // return $admin->role_id === 1;
        return $admin->role->name === 'Super Admin';
    }
}
