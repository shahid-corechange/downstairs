<?php

namespace App\Http\Traits;

use DB;

/**
 * Trait HasManySyncTrait, used to sync hasMany relationship records.
 *
 * Add new method to the model
 */
trait HasManySyncTrait
{
    /**
     * Sync HasMany relationship records.
     *
     * @param  string  $relation The name of the hasMany relationship
     * @param  array  $items Array of items to sync
     * @param  string  $foreignKey The foreign key used in the relationship
     * @param  callable|null  $createCallback Optional callback for custom creation logic
     * @param  callable|null  $updateCallback Optional callback for custom update logic
     * @param  callable|null  $deleteCallback Optional callback for custom delete logic
     * when item not in the provided array
     * @return $this
     */
    public function syncHasMany(
        $relation,
        $items,
        $foreignKey,
        $createCallback = null,
        $updateCallback = null,
        $deleteCallback = null,
    ) {
        return DB::transaction(function () use (
            $relation,
            $items,
            $foreignKey,
            $createCallback,
            $updateCallback,
            $deleteCallback,
        ) {
            // Get the relationship instance and related model
            $relationship = $this->$relation();

            // Get existing items
            $existingItems = $this->$relation;
            $itemIds = array_column($items, $foreignKey);

            // Delete items not in the provided array
            if ($deleteCallback) {
                $deleteCallback($itemIds, $items);
            } else {
                $relationship->whereNotIn($foreignKey, $itemIds)->delete();
            }

            // Update or create items
            foreach ($items as $item) {
                $id = $item[$foreignKey];
                $existingItem = $existingItems->firstWhere($foreignKey, $id);

                if ($existingItem) {
                    // Update existing item
                    if ($updateCallback) {
                        $updateCallback($existingItem, $item);
                    } else {
                        $existingItem->update($item);
                    }
                } else {
                    // Create new item
                    if ($createCallback) {
                        $createCallback($this, $item);
                    } else {
                        $relationship->create($item);
                    }
                }
            }

            return $this;
        });
    }
}
