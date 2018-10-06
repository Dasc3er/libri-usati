<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionUser extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
