<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlockDay extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        'block_date',
        'start_block_time',
        'end_block_time',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
