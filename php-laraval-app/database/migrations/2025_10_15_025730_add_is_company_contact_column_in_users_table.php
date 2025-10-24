<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_company_contact')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_company_contact')->default(false)->after('identity_number');
            });
        }

        // update users is_company_contact column to true
        User::where('status', 'active')
            ->whereExists(function ($query) {
                $query->select('*')
                    ->from('customers')
                    ->join('customer_user', 'customer_user.customer_id', '=', 'customers.id')
                    ->whereColumn('customer_user.user_id', 'users.id')
                    ->where('customers.membership_type', '=', 'company')
                    ->where('customers.type', '=', 'primary');
            })
            ->whereExists(function ($query) {
                $query->select('*')
                    ->from('roles')
                    ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', '=', User::class)
                    ->whereIn('roles.name', ['Customer']);
            })
            ->update(['is_company_contact' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_company_contact');
        });
    }
};
