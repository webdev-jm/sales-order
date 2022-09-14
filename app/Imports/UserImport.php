<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UserImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $email_arr = explode('@', $row[3]);
        $password = reset($email_arr).'123!';

        return new User([
            'firstname' => $row[0],
            'middlename' => $row[1],
            'lastname' => $row[2],
            'email' => $row[3],
            'password' => Hash::make($password),
            'group_code' => $row[4],
        ]);

        
    }
}
