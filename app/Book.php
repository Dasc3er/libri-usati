<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function concessions()
    {
        return $this->belongsToMany(User::class, 'concessions')->wherePivot('state', true);
    }

    public function requests()
    {
        return $this->belongsToMany(User::class, 'concessions')->wherePivot('state', false);
    }
}
