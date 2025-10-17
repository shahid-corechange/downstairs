<?php

namespace App\Models;

use App\Enums\Invoice\InvoiceStatusEnum;
use App\Http\Traits\SoftDeletesTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kolossal\Multiplex\HasMeta;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Str;

class RutCoApplicant extends Model implements CipherSweetEncrypted
{
    use HasFactory;
    use HasMeta;
    use SoftDeletesTrait;
    use UsesCipherSweet;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'identity_number',
        'phone',
        'dial_code',
        'pause_start_date',
        'pause_end_date',
        'is_enabled',
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_phone',
        'is_paused',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pause_start_date' => 'date',
        'pause_end_date' => 'date',
        'is_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Define the columns that always be returned.
     *
     * @var string[]
     */
    protected array $includes = ['id', 'identity_number'];

    /**
     * Define the columns from current model that is needed by the accessor.
     *
     * @var array<string,string[]>
     */
    protected array $accessorsFields = [
        'formatted_phone' => ['dial_code', 'phone'],
        'is_paused' => ['pause_start_date', 'pause_end_date'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'is_paused' => ['user.invoices' => ['month', 'year', 'status']],
    ];

    /**
     * Encrypted Fields
     *
     * Each column that should be encrypted should be added below. Each column
     * in the migration should be a `text` type to store the encrypted value.
     *
     * ```
     * ->addField('column_name')
     * ->addBooleanField('column_name')
     * ->addIntegerField('column_name')
     * ->addTextField('column_name')
     * ```
     *
     * A JSON array can be encrypted as long as the key structure is defined in
     * a field map. See the docs for details on defining field maps.
     *
     * ```
     * ->addJsonField('column_name', $fieldMap)
     * ```
     *
     * Each field that should be searchable using an exact match needs to be
     * added as a blind index. Partial search is not supported. See the docs
     * for details on bit sizes and how to use compound indexes.
     *
     * ```
     * ->addBlindIndex('column_name', new BlindIndex('column_name_index'))
     * ```
     *
     * @see https://github.com/spatie/laravel-ciphersweet
     * @see https://ciphersweet.paragonie.com/
     * @see https://ciphersweet.paragonie.com/php/blind-index-planning
     * @see https://github.com/paragonie/ciphersweet/blob/master/src/EncryptedRow.php
     */
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('identity_number')
            ->addBlindIndex('identity_number', new BlindIndex('identity_number_index'));
    }

    /**
     * Get the formatted co-applicant's phone number.
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (! $this->phone || $this->phone === $this->dial_code) {
            return '';
        }

        return Str::replaceFirst($this->dial_code, "+{$this->dial_code} ", $this->phone);
    }

    /**
     * Get the co-applicant's pause status.
     */
    public function getIsPausedAttribute(): bool
    {
        if (! $this->pause_start_date && ! $this->pause_end_date) {
            return false;
        }

        if ($this->pause_start_date && ! $this->pause_end_date) {
            return $this->pause_start_date->isPast();
        }

        /**
         * If the pause end date is in the past, check if the user's invoice on that month is already
         * created on Fortnox. If it's already created, then the co-applicant is not paused.
         **/
        if ($this->pause_start_date->isPast() && $this->pause_end_date->isPast()) {
            return $this->user->invoices
                ->where('month', $this->pause_end_date->format('m'))
                ->where('year', $this->pause_end_date->format('Y'))
                ->whereNotIn('status', [InvoiceStatusEnum::Open(), InvoiceStatusEnum::Cancel()])
                ->isEmpty();
        }

        return $this->pause_start_date->isPast() && $this->pause_end_date->isFuture();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeWhereIdentityNumber(Builder $query, string $identityNumber): void
    {
        $query->whereBlind('identity_number', 'identity_number_index', $identityNumber);
    }
}
