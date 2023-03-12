<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Admin::first()){
            print('Admin Record exists. Abborting seeder. ');
            return;
        }


        Admin::create([
            'name'=>'Admin',
            'email'=>'admin@mail.com',
            'password'=>Hash::make('AdminMakerChecker'),
        ]);

        Admin::create([
            'name'=>'Admin2',
            'email'=>'admin2@mail.com',
            'password'=>Hash::make('AdminMakerChecker'),
        ]);

        Admin::create([
            'name'=>'Admin3',
            'email'=>'admin3@mail.com',
            'password'=>Hash::make('AdminMakerChecker'),
        ]);
    }
}
