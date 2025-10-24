<?php

namespace App\Models;

use App\Enums\Auth\TokenAbilityEnum;
use App\Enums\Contact\ContactTypeEnum;
use App\Enums\Discount\CustomerDiscountTypeEnum;
use App\Enums\ScheduleEmployee\ScheduleEmployeeStatusEnum;
use App\Enums\User\UserStatusEnum;
use App\Http\Traits\SoftDeletesTrait;
use App\Notifications\ForgotPasswordNotification;
use Cache;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as Authorize;
use Illuminate\Contracts\Auth\Authenticatable as Authenticate;
use Illuminate\Contracts\Auth\CanResetPassword as ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail as VerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted as CipherSweet;
use Spatie\Permission\Traits\HasRoles;
use Str;

class User extends Model implements Authenticate, Authorize, CipherSweet, ResetPassword, VerifyEmail
{
    use Authenticatable;
    use AuthenticationLoggable;
    use Authorizable;
    use CanResetPassword;
    use CascadeSoftDeletes;
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Impersonate;
    use MustVerifyEmail;
    use Notifiable;
    use SoftDeletesTrait;
    use UsesCipherSweet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'cellphone',
        'dial_code',
        'identity_number',
        'password',
        'status',
        'email_verified_at',
        'cellphone_verified_at',
        'identity_number_verified_at',
        'last_seen',
        'is_company_contact',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'fullname',
        'initials',
        'formatted_cellphone',
        'total_credits',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cascade soft deletes.
     */
    protected $cascadeDeletes = [
        'subscriptions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_seen' => 'datetime',
        'email_verified_at' => 'datetime',
        'cellphone_verified_at' => 'datetime',
        'identity_number_verified_at' => 'datetime',
        'is_company_contact' => 'boolean',
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
        'fullname' => ['first_name', 'last_name'],
        'formatted_cellphone' => ['dial_code', 'cellphone'],
    ];

    /**
     * Define the relationships that an accessor uses.
     *
     * @var array<string,array<string,string[]>>
     */
    protected array $accessorsRelations = [
        'total_credits' => ['active_credits' => ['remaining_amount']],
        'is_company_contact' => ['primary_customer' => ['membership_type', 'identity_number']],
    ];

    /**
     * Generate hash for creating password.
     */
    public function generateCreatePasswordHash(): string
    {
        return sha1($this->email.'|'.$this->getKey().'|'.config('app.key'));
    }

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
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        scoped_localize($this->info->language, function () use ($token) {
            $this->notify(new ForgotPasswordNotification($token));
        });
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === UserStatusEnum::Active();
    }

    /**
     * Check if user is Administration
     */
    public function isSuperadmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    /**
     * Check if user is Employee
     */
    public function isEmployee(): bool
    {
        $count = count($this->roles()->get());

        if ($count > 1 || ($count === 1 && $this->roles()->first()->name !== 'Customer')) {
            return true;
        }

        return false;
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }

    /**
     * Create access token and refresh token for user.
     *
     * @return array<string,string>
     */
    public function generateTokens(bool $raw = false): array
    {
        $id = Str::random(6);
        $accessToken = $this->createToken(
            "{$id}_{$this->email}_access_token",
            [TokenAbilityEnum::APIAccess()],
            now()->addMinutes(config('sanctum.access_token_expiration'))
        );
        $refreshToken = $this->createToken(
            "{$id}_{$this->email}_refresh_token",
            [TokenAbilityEnum::IssueAccessToken()],
            now()->addMinutes(config('sanctum.refresh_token_expiration'))
        );

        Cache::set(
            'personal_access_token_'.hash('sha256', $accessToken->plainTextToken),
            $accessToken->accessToken,
            config('sanctum.access_token_expiration') * 60
        );
        Cache::set(
            'personal_access_token_'.hash('sha256', $refreshToken->plainTextToken),
            $refreshToken->accessToken,
            config('sanctum.refresh_token_expiration') * 60
        );

        return [
            'access_token' => $raw ? $accessToken : $accessToken->plainTextToken,
            'refresh_token' => $raw ? $refreshToken : $refreshToken->plainTextToken,
        ];
    }

    /**
     * Revoke tokens for user.
     */
    public function revokeTokens(): void
    {
        /** @var \App\Models\PersonalAccessToken */
        $currentToken = $this->currentAccessToken();

        // If the current token is refresh token
        if ($currentToken->can(TokenAbilityEnum::IssueAccessToken())) {
            /** @var \App\Models\PersonalAccessToken */
            $accessToken = $this->tokens()
                ->where('name', str_replace('refresh_token', 'access_token', $currentToken->name))
                ->first();

            // Prevent race condition
            if ($accessToken) {
                Cache::forget('personal_access_token_'.$accessToken->token);
                $accessToken->delete();
            }
        } else {
            /** @var \App\Models\PersonalAccessToken */
            $refreshToken = $this->tokens()
                ->where('name', str_replace('access_token', 'refresh_token', $currentToken->name))
                ->first();

            // Prevent race condition
            if ($refreshToken) {
                Cache::forget('personal_access_token_'.$refreshToken->token);
                $refreshToken->delete();
            }
        }

        Cache::forget('personal_access_token_'.$currentToken->token);
        $currentToken->delete();
    }

    /**
     * Get a fullname combination of first_name and last_name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute(): string
    {
        $first_name = $this->first_name ? mb_strtoupper(mb_substr($this->first_name, 0, 1)) : null;
        $last_name = $this->last_name ? mb_strtoupper(mb_substr($this->last_name, 0, 1)) : null;

        return $first_name.$last_name;
    }

    public function getFormattedCellphoneAttribute(): string
    {
        if (! $this->cellphone || $this->cellphone === $this->dial_code) {
            return '';
        }

        return Str::replaceFirst($this->dial_code, "+{$this->dial_code} ", $this->cellphone);
    }

    public function getLastSeenHumanAttribute()
    {
        if (! $this->last_seen) {
            return __('Never');
        }

        return $this->last_seen->diffForHumans();
    }

    public function getLastLoginAtHumanAttribute()
    {
        $lastSeen = $this->lastLoginAt();

        if (! $lastSeen) {
            return __('never');
        }

        return $lastSeen->diffForHumans();
    }

    public function getTotalCreditsAttribute(): int
    {
        return $this->activeCredits->sum('remaining_amount');
    }

    /**
     * SQL raw expression for fullname.
     */
    public function fullname(): string
    {
        return "TRIM(CONCAT_WS(' ', first_name, last_name))";
    }

    /**
     * User relation to info model.
     */
    public function info(): HasOne
    {
        return $this->hasOne(UserInfo::class)->withTrashed();
    }

    public function subscriptions(): HasMany
    {
        return $this->HasMany(Subscription::class);
    }

    /**
     * User relation to associations model.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * User relation to user property model.
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    public function feedbacks(): MorphMany
    {
        return $this->morphMany(Feedback::class, 'feedbackable');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class)->withTrashed();
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    public function activeCredits(): HasMany
    {
        return $this->hasMany(Credit::class)->valid();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * User schedule cleaning
     */
    public function scheduleCleanings(): HasManyThrough
    {
        return $this->hasManyThrough(ScheduleCleaning::class, Subscription::class);
    }

    /**
     * User schedule employees
     */
    public function scheduleEmployees(): HasMany
    {
        return $this->hasMany(ScheduleEmployee::class);
    }

    /**
     * User active schedule employees
     */
    public function activeScheduleEmployees(): HasMany
    {
        return $this->hasMany(ScheduleEmployee::class)
            ->whereIn(
                'status',
                [ScheduleEmployeeStatusEnum::Progress(), ScheduleEmployeeStatusEnum::Pending()]
            );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function fixedPrices(): HasMany
    {
        return $this->hasMany(FixedPrice::class);
    }

    public function customerDiscounts(): HasMany
    {
        return $this->hasMany(CustomerDiscount::class);
    }

    public function rutCoApplicants(): HasMany
    {
        return $this->hasMany(RutCoApplicant::class);
    }

    public function workHours(): HasMany
    {
        return $this->hasMany(WorkHour::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function primaryCustomer(): HasOneThrough
    {
        return $this->hasOneThrough(
            Customer::class,
            CustomerUser::class,
            'user_id',
            'id',
            'id',
            'customer_id'
        )
            ->where('type', ContactTypeEnum::Primary());
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_users');
    }

    public function cleaningDiscounts(): HasMany
    {
        return $this->customerDiscounts()->where('type', CustomerDiscountTypeEnum::Cleaning());
    }

    public function laundryDiscounts(): HasMany
    {
        return $this->customerDiscounts()->where('type', CustomerDiscountTypeEnum::Laundry());
    }
}
