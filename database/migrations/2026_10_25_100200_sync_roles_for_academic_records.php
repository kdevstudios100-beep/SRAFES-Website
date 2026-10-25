<?php

use App\Authorization\RoleRegistry;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Adds the academic-records permissions to existing roles
    public function up(): void
    {
        RoleRegistry::sync();
    }

    public function down(): void
    {
        // Permissions are left in place; srafes:sync-roles reconciles them with the registry
    }
};
