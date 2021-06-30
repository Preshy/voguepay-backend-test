<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallets extends Model
{
    public function deduct($account_id, $amount) {
        $balance = ($this->amount - $amount);

        $this->where('account_id', $account_id)->where('currency', 'NGN')->update(['amount' => $balance]);
    }
}
