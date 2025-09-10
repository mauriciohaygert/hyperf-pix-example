<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Concerns\CamelCase;

/**
 * @property string $account_withdraw_id
 * @property string $type
 * @property string $key
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AccountWithdrawPix extends Model
{
    use CamelCase;

    protected ?string $table = 'account_withdraw_pix';
    
    protected string $primaryKey = 'account_withdraw_id';
    
    protected string $keyType = 'string';
    
    public bool $incrementing = false;

    protected array $fillable = [
        'account_withdraw_id',
        'type',
        'key',
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function withdraw()
    {
        return $this->belongsTo(AccountWithdraw::class, 'account_withdraw_id', 'id');
    }

    public function isValidEmail(): bool
    {
        return $this->type === 'email' && filter_var($this->key, FILTER_VALIDATE_EMAIL) !== false;
    }
}
