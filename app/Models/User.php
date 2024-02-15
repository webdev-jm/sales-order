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
        'department_id',
        'firstname',
        'middlename',
        'lastname',
        'email',
        'notify_email',
        'password',
        'group_code',
        'last_activity',
        'status',
        'coe',
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

    public function department() {
        return $this->belongsTo('App\Models\Department');
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

    public function activity_plans() {
        return $this->hasMany('App\Models\ActivityPlan');
    }

    public function deviations() {
        return $this->hasMany('App\Models\Deviation');
    }

    public function weekly_activity_reports() {
        return $this->hasMany('App\Models\WeeklyActivityReport');
    }

    public function trips() {
        return $this->hasMany('App\Models\ActivityPlanDetailTrip');
    }

    public function department_structures() {
        return $this->hasMany('App\Models\DepartmentStructure');
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
        $org_structures = $this->organizations;
        $subordinate_ids = [];
        foreach($org_structures as $org_structure) {
            $structures = OrganizationStructure::where('reports_to_id', $org_structure->id)
            ->get();
            foreach($structures as $structure) {
                $this->getSubordinateIdsRecursive($structure, $subordinate_ids);
            }
        }

        return $subordinate_ids;
    }

    private function getSubordinateIdsRecursive($subordinate, &$subordinate_ids, $level = 1) {
        if(!empty($subordinate->user_id)) {
            $subordinate_ids['level_'.$level][] = $subordinate->user_id;
        }
        if($level >= 5) {
            return;
        }
        $subordinates = OrganizationStructure::where('reports_to_id', $subordinate->id)
        ->get();
        foreach($subordinates as $subordinate) {
            $this->getSubordinateIdsRecursive($subordinate, $subordinate_ids, $level + 1);
        }
    }

    public function getSupervisorIds() {
        $organizations = $this->organizations;
        $supervisor_ids = [];
    
        // define the supervisor levels we want to fetch
        $supervisor_levels = ['first', 'second', 'third', 'fourth', 'fifth', 'sixth'];
    
        foreach($organizations as $organization) {
            if(!empty($organization->reports_to_id)) {
                // fetch the supervisor data with eager loading
                $supervisor = OrganizationStructure::where('id', $organization->reports_to_id)
                    ->first();
    
                // loop over the supervisor levels and fetch the supervisor data for each level
                foreach($supervisor_levels as $level => $level_name) {
                    if(!empty($supervisor)) {
                        // store the supervisor id for the current level
                        $supervisor_ids[$level_name] = $supervisor->user_id;
    
                        // move to the next level of supervisor
                        $supervisor = OrganizationStructure::where('id', $supervisor->reports_to_id)
                            ->first();
                    } else {
                        // break the loop if there are no more supervisors at this level
                        break;
                    }
                }
            }
        }
    
        // remove null values
        $supervisor_ids = array_filter($supervisor_ids, function($value) {
            return !is_null($value);
        });

        // remove duplicate id
        return array_unique($supervisor_ids);
    }

    public function getImmediateSuperiorId() {
        $supervisor_ids = $this->getSupervisorIds();
        // get first value
        return reset($supervisor_ids);
    }

    public function getDepartmentSubordinates() {
        $department = $this->department;
    }

    public function getDepartmentSupervisorIds() {
        $department_structures = $this->department_structures;

        $supervisor_ids = [];
    
        // define the supervisor levels we want to fetch
    
        foreach($department_structures as $structure) {
            if(!empty($structure->reports_to_ids)) {
                $reports_to_arr = explode(',', $structure->reports_to_ids);
                foreach($reports_to_arr as $reports_to_id) {
                    // fetch the supervisor data with eager loading
                    $supervisor = DepartmentStructure::where('id', $reports_to_id)
                        ->first();
                    if(!empty($supervisor)) {
                        $supervisor_ids[] = $supervisor->user_id;
                    } else {
                        // break the loop if there are no more supervisors at this level
                        break;
                    }
                }
                
            }
        }
    
        // remove null values
        $supervisor_ids = array_filter($supervisor_ids, function($value) {
            return !is_null($value);
        });

        // remove duplicate id
        return array_unique($supervisor_ids);
    }
    
}
