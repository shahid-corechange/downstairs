<?php

use App\Enums\Service\ServiceTypeEnum;
use App\Models\Service;
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
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->renameColumn('type', 'membership_type');
            $table->index('membership_type');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('type')->index()->after('fortnox_article_id');
        });

        $laundryServiceIds = [
            config('downstairs.services.laundry.private.id'),
            config('downstairs.services.laundry.company.id'),
        ];

        // Fill with laundry type
        Service::withTrashed()
            ->whereIn('id', $laundryServiceIds)
            ->update([
                'type' => ServiceTypeEnum::Laundry(),
            ]);

        // Fill with cleaning type
        Service::withTrashed()
            ->whereNotIn('id', $laundryServiceIds)
            ->update([
                'type' => ServiceTypeEnum::Cleaning(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['membership_type']);
            $table->dropColumn('type');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('membership_type', 'type');
            $table->index('type');
        });
    }
};
