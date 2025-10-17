<?php

use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Models\OrderRow;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $serviceArticleIds = Service::all()->pluck('fortnox_article_id')->toArray();
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderRow> $orderRows */
        $orderRows = OrderRow::whereIn('fortnox_article_id', $serviceArticleIds)
            ->whereHas('order', function (Builder $query) {
                $query->where('status', OrderStatusEnum::Draft());
            })
            ->get();

        DB::transaction(function () use ($orderRows) {
            foreach ($orderRows as $row) {
                $row->update([
                    ...$row->toArray(),
                    'quantity' => $row->quantity / 4,
                    'unit' => ProductUnitEnum::Hours(),
                    'price' => $row->price * 4,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
