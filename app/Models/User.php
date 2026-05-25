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

use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     * This supports multi-tenant deployments where each client has a separate database.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default'));
    }

    protected $fillable = [
        'hub_user_id',
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
        'db_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function routeNotificationForMail($notification)
    {
        return $this->notify_email;
    }

    public function adminlte_profile_url()
    {
        return route('profile');
    }

    public function adminlte_desc()
    {
        return '';
    }

    /**
     * Return the user's full name in title case.
     * Attributes are already loaded on $this, so no extra query is needed.
     */
    public function fullName(): string
    {
        $name = $this->firstname . ' ' . $this->lastname;

        if (!empty($this->middlename)) {
            $name = $this->firstname . ' ' . $this->middlename . ' ' . $this->lastname;
        }

        return ucwords(strtolower($name));
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function account_logins()
    {
        return $this->hasMany(AccountLogin::class);
    }

    public function branch_logins()
    {
        return $this->hasMany(BranchLogin::class);
    }

    public function sales_person()
    {
        return $this->hasMany(SalesPerson::class);
    }

    public function schedules()
    {
        return $this->hasMany(UserBranchSchedule::class);
    }

    public function logged_account()
    {
        return $this->account_logins()->whereNull('time_out')->first();
    }

    public function logged_branch()
    {
        return $this->branch_logins()->whereNull('time_out')->first();
    }

    public function organizations()
    {
        return $this->hasMany(OrganizationStructure::class);
    }

    public function cost_centers()
    {
        return $this->hasMany(CostCenter::class);
    }

    public function districts()
    {
        return $this->belongsToMany(District::class);
    }

    public function territories()
    {
        return $this->hasMany(Territory::class);
    }

    public function activity_plans()
    {
        return $this->hasMany(ActivityPlan::class);
    }

    public function deviations()
    {
        return $this->hasMany(Deviation::class);
    }

    public function weekly_activity_reports()
    {
        return $this->hasMany(WeeklyActivityReport::class);
    }

    public function trips()
    {
        return $this->hasMany(ActivityPlanDetailTrip::class);
    }

    public function department_structures()
    {
        return $this->hasMany(DepartmentStructure::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }

    public function scopeUserSearch($query, $search, $limit)
    {
        return $query
            ->orderBy('id', 'DESC')
            ->when($search != '', function ($q) use ($search) {
                $q->where('firstname', 'like', '%' . $search . '%')
                    ->orWhere('middlename', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->paginate($limit)
            ->onEachSide(1)
            ->appends(request()->query());
    }

    public function scopeUserAjax($query, $search)
    {
        $users = $query->select('id', 'firstname', 'lastname')
            ->when($search != '', function ($q) use ($search) {
                $q->where('firstname', 'like', '%' . $search . '%')
                    ->orWhere('lastname', 'like', '%' . $search . '%');
            })
            ->limit(5)
            ->get();

        return $users->map(fn($user) => [
            'id'   => $user->id,
            'text' => $user->firstname . ' ' . $user->lastname,
        ])->all();
    }

    /**
     * Walk the OrganizationStructure tree downward from this user to collect
     * subordinate user IDs, grouped by depth level (level_1, level_2, â€¦).
     * Recursion is capped at 5 levels to avoid runaway queries on deep hierarchies.
     */
    public function getSubordinateIds(): array
    {
        $org_structures  = $this->organizations;
        $subordinate_ids = [];

        foreach ($org_structures as $org_structure) {
            $structures = OrganizationStructure::where('reports_to_id', $org_structure->id)->get();
            foreach ($structures as $structure) {
                $this->getSubordinateIdsRecursive($structure, $subordinate_ids);
            }
        }

        return $subordinate_ids;
    }

    private function getSubordinateIdsRecursive($subordinate, &$subordinate_ids, $level = 1): void
    {
        if (!empty($subordinate->user_id)) {
            $subordinate_ids['level_' . $level][] = $subordinate->user_id;
        }

        if ($level >= 5) {
            return;
        }

        $subordinates = OrganizationStructure::where('reports_to_id', $subordinate->id)->get();
        foreach ($subordinates as $sub) {
            $this->getSubordinateIdsRecursive($sub, $subordinate_ids, $level + 1);
        }
    }

    /**
     * Walk the OrganizationStructure tree upward from this user to collect
     * supervisor user IDs, keyed by level name (first, second, â€¦, sixth).
     * Capped at six levels â€” matches the maximum org-chart depth in the system.
     */
    public function getSupervisorIds(): array
    {
        $organizations    = $this->organizations;
        $supervisor_ids   = [];
        $supervisor_levels = ['first', 'second', 'third', 'fourth', 'fifth', 'sixth'];

        foreach ($organizations as $organization) {
            if (empty($organization->reports_to_id)) {
                continue;
            }

            $supervisor = OrganizationStructure::where('id', $organization->reports_to_id)->first();

            foreach ($supervisor_levels as $level => $level_name) {
                if (empty($supervisor)) {
                    break;
                }

                $supervisor_ids[$level_name] = $supervisor->user_id;
                $supervisor = OrganizationStructure::where('id', $supervisor->reports_to_id)->first();
            }
        }

        return array_unique(array_filter($supervisor_ids, fn($value) => !is_null($value)));
    }

    public function getImmediateSuperiorId()
    {
        $supervisor_ids = $this->getSupervisorIds();
        return reset($supervisor_ids);
    }

    public function getDepartmentSupervisorIds(): array
    {
        $department_structures = $this->department_structures;
        $supervisor_ids        = [];

        foreach ($department_structures as $structure) {
            if (empty($structure->reports_to_ids)) {
                continue;
            }

            foreach (explode(',', $structure->reports_to_ids) as $reports_to_id) {
                $supervisor = DepartmentStructure::where('id', $reports_to_id)->first();
                if (!empty($supervisor)) {
                    $supervisor_ids[] = $supervisor->user_id;
                }
            }
        }

        return array_unique(array_filter($supervisor_ids, fn($value) => !is_null($value)));
    }
}

