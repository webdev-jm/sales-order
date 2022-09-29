<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'password',
        'group_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function adminlte_profile_url()
    {
        return route('profile');
    }

    public function adminlte_desc() {
        return '';
    }

    public function accounts() {
        return $this->belongsToMany('App\Models\Account');
    }

    public function branches() {
        return $this->belongsToMany('App\Models\Branch');
    }

    public function account_logins() {
        return $this->hasMany('App\Models\AccountLogin');
    }

    public function sales_person() {
        return $this->hasMany('App\Models\SalesPerson');
    }

    public function logged_account() {
        return $this->account_logins()->whereNull('time_out')->first();
    }

    public function scopeUserSearch($query, $search, $limit) {
        if($search != '') {
            $users = $query->orderBy('id', 'DESC')
            ->where('firstname', 'like', '%'.$search.'%')
            ->orWhere('middlename', 'like', '%'.$search.'%')
            ->orWhere('lastname', 'like', '%'.$search.'%')
            ->orWhere('email', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $users = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $users;
    }

    public function scopeUserAjax($query, $search) {
        if($search == '') {
            $users = $query->select('id', 'firstname', 'lastname')->limit(5)->get();
        } else {
            $users = $query->select('id', 'firstname', 'lastname')
            ->where('firstname', 'like', '%'.$search.'%')
            ->orWhere('lastname', 'like', '%'.$search.'%')
            ->limit(5)->get();
        }

        $response = [];
        foreach($users as $user) {
            $response[] = array(
                'id' => $user->id,
                'text' => $user->firstname.' '.$user->lastname
            );
        }

        return $response;
    }
}
