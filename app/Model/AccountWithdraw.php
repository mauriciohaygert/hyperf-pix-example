<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Concerns\CamelCase;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $account_id
 * @property string $method
 * @property float $amount
 * @property bool $scheduled
 * @property Carbon|null $scheduled_for
 * @property bool $done
 * @property bool $error
 * @property string|null $error_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AccountWithdraw extends Model
{
    use CamelCase;

    protected ?string $table = 'account_withdraw';
    
    protected string $keyType = 'string';
    
    public bool $incrementing = false;

    protected array $fillable = [
        'id',
        'account_id',
        'method',
        'amount',
        'scheduled',
        'scheduled_for',
        'done',
        'error',
        'error_reason',
    ];

    protected array $casts = [
        'amount' => 'decimal:2',
        'scheduled' => 'boolean',
        'done' => 'boolean',
        'error' => 'boolean',
        'scheduled_for' => 'datetime',
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

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function pix()
    {
        return $this->hasOne(AccountWithdrawPix::class, 'account_withdraw_id', 'id');
    }

    public function markAsDone(): bool
    {
        $this->done = true;
        return $this->save();
    }

    public function markAsError(string $reason): bool
    {
        $this->error = true;
        $this->error_reason = $reason;
        $this->done = true;
        return $this->save();
    }

    public function isScheduled(): bool
    {
        return $this->scheduled && $this->scheduled_for !== null;
    }

    public function isReadyToProcess(): bool
    {
        if (!$this->isScheduled()) {
            return true;
        }

        return $this->scheduled_for <= Carbon::now(config('app_timezone'));
    }
}
