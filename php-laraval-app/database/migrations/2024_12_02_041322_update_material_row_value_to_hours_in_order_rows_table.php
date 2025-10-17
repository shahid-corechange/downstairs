<?php

use App\Enums\Order\OrderStatusEnum;
use App\Enums\Product\ProductUnitEnum;
use App\Models\OrderRow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! in_array(app()->environment(), ['local', 'testing'])) {
            $material = get_material();
            $materialFortnoxArticleId = $material ? $material->fortnox_article_id : null;
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderRow> $orderRows */
            $orderRows = OrderRow::where('fortnox_article_id', $materialFortnoxArticleId)
                ->whereHas('order', function (Builder $query) {
                    $query->where('status', OrderStatusEnum::Draft());
                })
                ->get();

            DB::transaction(function () use ($orderRows) {
                foreach ($orderRows as $row) {
                    $row->update([
                        'order_id' => $row->order_id,
                        'fortnox_article_id' => $row->fortnox_article_id,
                        'description' => $row->description,
                        'quantity' => $row->quantity / 4,
                        'unit' => ProductUnitEnum::Piece(),
                        'price' => $row->price * 4,
                        'discount_percentage' => $row->discount_percentage,
                        'vat' => $row->vat,
                        'has_rut' => $row->has_rut,
                        'internal_note' => $row->internal_note,
                    ]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
