<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric','min:10', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'key' => ['required', 'string', 'max:5'],
            'password' => $this->passwordRules(),
         
        ],[
            'name.required'=>'نام الزامی است ',
            'email.required'=>'ایمیل الزامی است',
            'phone.required'=>'تلفن همراه الزامی است',
            'phone.unique'=>'با این شماره قبلا ثبت نام شده',
            'email.unique'=>'با این ایمیل قبلا ثبت نام شده',
            'password.min'=>'رمز عبور حداقل باید 8 رقمی باشد',
        ])->validate();
        $req = DB::table('reg_req')->where('phone', $input['phone'])->where('key', $input['key'])->get();
           if($req[0]->key == $input['key']){
               return User::create([
                   'name' => $input['name'],
                   'email' => $input['email'],
                   'phone' => $input['phone'],
                   'password' => Hash::make($input['password']),
                ]);
            }  

    }
}
