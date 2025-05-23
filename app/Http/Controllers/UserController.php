<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\Company;
use App\Models\Department;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;

use App\Http\Traits\GlobalTrait;

class UserController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $users = User::UserSearch($search, $this->setting->data_per_page);
        return view('users.index')->with([
            'users' => $users,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::orderBy('name', 'ASC')->get();
        
        $departments = Department::orderBy('department_code', 'ASC')->get();
        $department_options = [
            '' => '- Select department -'
        ];
        foreach($departments as $department) {
            $department_options[$department->id] = '['.$department->department_code.'] '.$department->department_name;
        }

        return view('users.create')->with([
            'roles' => $roles,
            'departments' => $department_options
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $email_arr = explode('@', $request->email);
        $password = reset($email_arr).'123!';
        
        $user = new User([
            'department_id' => $request->department_id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'notify_email' => $request->notify_email,
            'password' => Hash::make($password),
            'group_code' => $request->group_code,
            'coe' => $request->coe,
        ]);
        $user->save();

        $user->assignRole($request->roles);

        // logs
        activity('create')
        ->performedOn($user)
        ->log(':causer.firstname :causer.lastname has created user :subject.firstname :subject.lastname .');

        return redirect()->route('user.index')->with([
            'message_success' => 'User '.$user->firstname.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('users.show')->with([
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::orderBy('name', 'ASC')->get();

        $departments = Department::orderBy('department_code', 'ASC')->get();
        $department_options = [
            '' => '- Select department -'
        ];
        foreach($departments as $department) {
            $department_options[$department->id] = '['.$department->department_code.'] '.$department->department_name;
        }

        return view('users.edit')->with([
            'user' => $user,
            'roles' => $roles,
            'departments' => $department_options
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $changes_arr['old'] = $user->getOriginal();

        $user->update([
            'department_id' => $request->department_id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'notify_email' => $request->notify_email,
            'group_code' => $request->group_code,
            'coe' => $request->coe,
        ]);

        $user->syncRoles($request->roles);

        $model = $user;
        $changes_arr['changes'] = $user->getChanges();

        // logs
        activity('update')
        ->performedOn($model)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated user :subject.firstname :subject.lastname .');

        return back()->with([
            'message_success' => 'User '.$user->firstname.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        $accounts = Account::all();
        $bevi_accounts = Account::whereHas('company', function($qry) {
            $qry->where('name', 'BEVI');
        })->get('id');

        $imports = Excel::toArray(new UserImport, $request->upload_file);
        foreach($imports[0] as $row) {
            $email_arr = explode('@', $row[3]);
            $password = reset($email_arr).'123!';

            $user = new User([
                'firstname' => $row[0],
                'middlename' => $row[1],
                'lastname' => $row[2],
                'email' => $row[3],
                'password' => Hash::make($password),
                'group_code' => $row[4],
            ]);
            $user->save();

            $user->assignRole('user');

            if($row[4] == 'NKA') {
                $user->accounts()->sync($bevi_accounts->pluck('id'));
            } else if($row[4] == 'CMD') {
                $user->accounts()->sync($accounts->pluck('id'));
            }
        }

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded users');

        return back()->with([
            'message_success' => 'Users has been uploaded.'
        ]);
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $response = User::UserAjax($search);
        return response()->json($response);
    }

    public function getAjax($id) {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
}
