<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Trait SoftDeletesTrait
 *
 * Add new method to the model
 */
trait SoftDeletesTrait
{
    use SoftDeletes;

    /**
     * Soft delete and retain ciphersweet blind index
     */
    public function softDelete()
    {
        $this->deleted_at = now();

        return $this->save();
    }
}
