<?php

namespace App\Models;

use App\Enums\MembershipTypeEnum;
use App\Http\Traits\SoftDeletesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Kolossal\Multiplex\HasMeta;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Str;

class Customer extends Model implements CipherSweetEncrypted
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
        'customer_ref_id',
        'address_id',
        'membership_type',
        'type',
        'identity_number',
        'name',
        'email',
        'phone1',
        'dial_code',
        'due_days',
        'invoice_method',
        'reference',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'company_contact_users',
        'formatted_phone1',
        'is_full',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
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
        'is_full' => ['email', 'address_id'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'company_contact_users' => ['companyContactUsers' => ['id']],
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

        // $encryptedRow
        //     ->addField('fortnox_id')
        //     ->addBlindIndex('fortnox_id', new BlindIndex('fortnox_id_index'));
    }

    /**
     * Get the users that act as contact for the company customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<array-key, User>
     */
    public function getCompanyContactUsersAttribute()
    {
        return $this->companyContactUsersQuery;
    }

    /**
     * Get the formatted customer's phone number.
     */
    public function getFormattedPhone1Attribute(): string
    {
        if (! $this->phone1 || $this->phone1 === $this->dial_code) {
            return '';
        }

        if (Str::startsWith($this->phone1, $this->dial_code)) {
            return Str::replaceFirst($this->dial_code, "+{$this->dial_code} ", $this->phone1);
        }

        return "+{$this->dial_code} {$this->phone1}";
    }

    public function getIsFullAttribute(): bool
    {
        return $this->email && $this->address_id && $this->identity_number !== '';
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function customerRef(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_ref_id')->withTrashed();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTrashed();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function companyUser(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            CustomerUser::class,
            'customer_id',
            'id',
            'id',
            'user_id'
        )
            ->withTrashed()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Company');
            });
    }

    public function companyContactUsersQuery(): BelongsToMany
    {
        return $this->users()
            ->withTrashed()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'Company');
            });
    }

    public function isPrivate(): bool
    {
        return $this->membership_type === MembershipTypeEnum::Private();
    }

    public function isCompany(): bool
    {
        return $this->membership_type === MembershipTypeEnum::Company();
    }
}
