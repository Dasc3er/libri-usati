<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'role'
    ];


    public function logs()
    {
        return $this->hasMany(Logs::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function authors()
    {
        return $this->hasMany(Author::class);
    }

    public function settings()
    {
        return $this->hasMany(OptionUser::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Book::class, 'concessions')->wherePivot('state', true);
    }

    public function contactRequests()
    {
        return $this->belongsToMany(Book::class, 'concessions')->wherePivot('state', false);
    }

    public function cascadeDelete()
    {
        $this->books()->each(function ($book) {
            $book->delete();
        });

        $this->delete();
    }

    /**
     * Set the user's role.
     *
     * @param string $value
     */
    public function setRoleAttribute($value)
    {
        if (strtolower($value) == 'admin' || $value == 1) {
            $this->attributes['role'] = 1;
        } else {
            $this->attributes['role'] = 0;
        }
    }

    public function isAdmin()
    {
        return !empty($this->role);
    }

    public function isBookConcessed($book)
    {
        $books = $this->concessions()->get();

        $list = array_pluck($books, 'id');

        return in_array($book->id, $list);
    }

    public function isBookRequested($book)
    {
        $books = $this->requests()->get();

        $list = array_pluck($books, 'id');

        return in_array($book->id, $list);
    }

    /**
     * Controlla che l'username inserito sia univoco.
     *
     * @param string $username       Username da controllare
     * @param bool   $ignore_current Ignora o meno l'utente attuale
     *
     * @return bool
     */
    public static function isUsernameFree($username, $ignore_current = true)
    {
        $where = [['username', '=', $username]];

        $auth = auth();
        if ($auth->check() && !empty($ignore_current)) {
            $where[] = ['id', '!=', $auth->user()->id];
        }

        return self::where($where)->count() == 0;
    }

    /**
     * Controlla che l'username inserito sia univoco.
     *
     * @param string $username       Username da controllare
     * @param bool   $ignore_current Ignora o meno l'utente attuale
     *
     * @return bool
     */
    public static function isEmailFree($email, $ignore_current = true)
    {
        $where = [['email', '=', $email]];

        $auth = auth();
        if ($auth->check() && !empty($ignore_current)) {
            $where[] = ['id', '!=', $auth->getUser()->id];
        }

        return self::where($where)->count() == 0;
    }
}
