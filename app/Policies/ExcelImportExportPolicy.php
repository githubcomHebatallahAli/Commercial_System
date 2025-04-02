<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExcelImportExportPolicy
{
    use HandlesAuthorization;

    public function importProducts(Admin $admin)
    {
        return in_array($admin->role_id, [1, 2]);
    }

    public function exportProducts(Admin $admin)
    {
        return in_array($admin->role_id, [1, 2]);
    }
}
