<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OldOrder extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'old_order_id',
    ];

    /**
     * Old order relation to orders model.
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }
}
