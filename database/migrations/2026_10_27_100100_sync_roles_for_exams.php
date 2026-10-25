<?php

use App\Authorization\RoleRegistry;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Adds the exam permissions to existing roles
    public function up(): void
    {
        RoleRegistry::sync();
    }

    public function down(): void
    {
        // Permissions are left in place; srafes:sync-roles reconciles them with the registry
    }
};
