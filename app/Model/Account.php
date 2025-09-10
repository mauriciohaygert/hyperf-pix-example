<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Concerns\CamelCase;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $name
 * @property float $balance
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Account extends Model
{
    use CamelCase;

    protected ?string $table = 'account';
    
    protected string $keyType = 'string';
    
    public bool $incrementing = false;

    protected array $fillable = [
        'id',
        'name',
        'balance',
    ];

    protected array $casts = [
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function save(array $options = []): bool
    {
        if (!$this->exists && empty($this->getKey())) {
            $this->{$this->getKeyName()} = Uuid::uuid4()->toString();
        }

        return parent::save($options);
    }

    public function withdraws()
    {
        return $this->hasMany(AccountWithdraw::class, 'account_id', 'id');
    }

    public function hasBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function debitBalance(float $amount): bool
    {
        if (!$this->hasBalance($amount)) {
            return false;
        }

        $this->balance -= $amount;
        return $this->save();
    }
}
