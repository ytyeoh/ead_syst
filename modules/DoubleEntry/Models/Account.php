<?php

namespace Modules\DoubleEntry\Models;

use App\Models\Model;
use Bkwld\Cloner\Cloneable;
use Date;

class Account extends Model
{
    use Cloneable;

    protected $table = 'double_entry_accounts';

    protected $appends = ['debit_total', 'credit_total'];

    protected $fillable = ['company_id', 'type_id', 'code', 'name', 'description', 'parent', 'enabled'];

    public function type()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\Type');
    }

    public function bank()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\AccountBank', 'id', 'account_id');
    }

    public function tax()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\AccountTax', 'id', 'account_id');
    }

    public function ledgers()
    {
        $ledgers = $this->hasMany('Modules\DoubleEntry\Models\Ledger');
    
        if (request()->has('start_date')) {
            $start_date = request('start_date') . ' 00:00:00';
            $end_date = request('end_date') . ' 23:59:59';

            $ledgers->whereBetween('issued_at', [$start_date, $end_date]);
        }
    
        return $ledgers;
    }

    /**
     * Scope code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Get the name including rate.
     *
     * @return string
     */
    public function getDebitTotalAttribute()
    {
        return $this->ledgers()->sum('debit');
    }

    /**
     * Get the name including rate.
     *
     * @return string
     */
    public function getCreditTotalAttribute()
    {
        return $this->ledgers()->sum('credit');
    }
    
    public function getOpeningBalanceAttribute()
    {
        $ledgers = $this->hasMany('Modules\DoubleEntry\Models\Ledger');

        $ledgers->whereDate('issued_at', '<', request()->get('start_date', Date::now()->startOfYear()->format('Y-m-d')));
        
        $balance = abs($ledgers->sum('debit') - $ledgers->sum('credit'));
        
        return $balance;
    }
}
