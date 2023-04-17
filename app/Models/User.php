<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Support\Facades\DB;

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
        'notify_email',
        'password',
        'group_code',
        'last_activity',
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

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    
    public function routeNotificationForMail($notification)
    {
        // Return email address only...
        return $this->notify_email;
 
        // Return email address and name...
        // return [$this->email_address => $this->name];
    }

    public function adminlte_profile_url()
    {
        return route('profile');
    }

    public function adminlte_desc() {
        return '';
    }

    public function fullName() {
        $name = $this->firstname.' '.$this->lastname;
        if(!empty($this->middlename)) {
            $name = $this->firstname.' '.$this->middlename.' '.$this->lastname;
        }
        
        return ucwords(strtolower($name));
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

    public function branch_logins() {
        return $this->hasMany('App\Models\BranchLogin');
    }

    public function sales_person() {
        return $this->hasMany('App\Models\SalesPerson');
    }

    public function schedules() {
        return $this->hasMany('App\Models\UserBranchSchedule');
    }

    public function logged_account() {
        return $this->account_logins()->whereNull('time_out')->first();
    }

    public function logged_branch() {
        return $this->branch_logins()->whereNull('time_out')->first();
    }

    public function organizations() {
        return $this->hasMany('App\Models\OrganizationStructure');
    }

    public function cost_centers() {
        return $this->hasMany('App\Models\CostCenter');
    }

    public function districts() {
        return $this->belongsToMany('App\Models\District');
    }

    public function territories() {
        return $this->hasMany('App\Models\Territory');
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

    public function getSubordinateIds() {
        $organizations = $this->organizations;
        $subordinate_ids = [];
        foreach($organizations as $organization) {
            $subordinates = DB::table('organization_structures')->where('reports_to_id', $organization->id)
            ->get();
            foreach($subordinates as $subordinate) {
                if(!empty($subordinate->user_id)) {
                    $subordinate_ids['first'][] = $subordinate->user_id;
                }
                // get second level subordinates
                $subordinates2 = DB::table('organization_structures')->where('reports_to_id', $subordinate->id)
                ->get();
                foreach($subordinates2 as $subordinate2) {
                    if(!empty($subordinate2->user_id)) {
                        $subordinate_ids['second'][] = $subordinate2->user_id;
                    }
                    // get third level subordinates
                    $subordinates3 = DB::table('organization_structures')->where('reports_to_id', $subordinate2->id)
                    ->get();
                    foreach($subordinates3 as $subordinate3) {
                        if(!empty($subordinate3->user_id)) {
                            $subordinate_ids['third'][] = $subordinate3->user_id;
                        }
                        // get fourth level subordinates
                        $subordinates4 = DB::table('organization_structures')->where('reports_to_id', $subordinate3->id)
                        ->get();
                        foreach($subordinates4 as $subordinate4) {
                            if(!empty($subordinate4->user_id)) {
                                $subordinate_ids['fourth'][] = $subordinate4->user_id;
                            }
                            // get fifth level subordinates
                            $subordinates5 = DB::table('organization_structures')->where('reports_to_id', $subordinate4->id)
                            ->get();
                            foreach($subordinates5 as $subordinate5) {
                                if(!empty($subordinate5->user_id)) {
                                    $subordinate_ids['fifth'][] = $subordinate5->user_id;
                                }
                            }
                        }
                    }
                }
            }
        }

        // return and remove duplicates
        return array_filter($subordinate_ids);
    }

    public function getSupervisorIds() {
        $organizations = $this->organizations;
        $supervisor_ids = [];
        foreach($organizations as $organization) {
            // check if has supervisor
            if(!empty($organization->reports_to_id)) {
                // first level
                $supervisor = DB::table('organization_structures')
                ->where('id', $organization->reports_to_id)
                ->first();
                $supervisor_ids['first'] = $supervisor->user_id;

                if(!empty($supervisor->reports_to_id)) {
                    // second level
                    $supervisor1 = DB::table('organization_structures')
                    ->where('id', $supervisor->reports_to_id)
                    ->first();
                    $supervisor_ids['second'] = $supervisor1->user_id;

                    if(!empty($supervisor1->reports_to_id)) {
                        // third level
                        $supervisor2 = DB::table('organization_structures')
                        ->where('id', $supervisor1->reports_to_id)
                        ->first();
                        $supervisor_ids['third'] = $supervisor2->user_id;
                        
                        if(!empty($supervisor2->reports_to_id)) {
                            // fourth level
                            $supervisor3 = DB::table('organization_structures')
                            ->where('id', $supervisor2->reports_to_id)
                            ->first();
                            $supervisor_ids['fourth'] = $supervisor3->user_id;

                            if(!empty($supervisor3->reports_to_id)) {
                                //fifth level
                                $supervisor4 = DB::table('organization_structures')
                                ->where('id', $supervisor3->reports_to_id)
                                ->first();
                                $supervisor_ids['fifth'] = $supervisor4->user_id;
                            }
                        }
                    }
                }
            }
        }

        // return and remove duplicates
        return array_unique(array_filter($supervisor_ids));
    }

    public function getSupervisorIds1() {
        $organizations = $this->organizations;
        $supervisor_ids = [];
    
        // define the supervisor levels we want to fetch
        $supervisor_levels = ['first', 'second', 'third', 'fourth', 'fifth'];
    
        foreach($organizations as $organization) {
            if(!empty($organization->reports_to_id)) {
                // fetch the supervisor data with eager loading
                $supervisor = OrganizationStructure::with('supervisor')
                    ->where('id', $organization->reports_to_id)
                    ->first();
    
                // loop over the supervisor levels and fetch the supervisor data for each level
                foreach($supervisor_levels as $level => $level_name) {
                    if(!empty($supervisor->supervisor)) {
                        // store the supervisor id for the current level
                        $supervisor_ids[$level_name] = $supervisor->supervisor->user_id;
    
                        // move to the next level of supervisor
                        $supervisor = $supervisor->supervisor;
                    } else {
                        // break the loop if there are no more supervisors at this level
                        break;
                    }
                }
            }
        }
    
        // remove duplicates and empty values and return the result
        return array_values(array_unique(array_filter($supervisor_ids)));
    }
    
}
