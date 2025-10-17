<?php

namespace App\Models;

use App\Http\Traits\SoftDeletesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kolossal\Multiplex\HasMeta;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Str;

class Employee extends Model implements CipherSweetEncrypted
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
        'fortnox_id',
        'user_id',
        'address_id',
        'identity_number',
        'name',
        'email',
        'phone1',
        'dial_code',
        'is_valid_identity',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_phone1',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_valid_identity' => 'boolean',
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
        'formatted_phone1' => ['dial_code', 'phone1'],
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
     * Get the formatted employee's phone number.
     */
    public function getFormattedPhone1Attribute(): string
    {
        if (! $this->phone1) {
            return '';
        }

        return Str::replaceFirst($this->dial_code, "+{$this->dial_code} ", $this->phone1);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
