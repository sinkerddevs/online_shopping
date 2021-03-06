<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Auth;
use Session;
use App\Cart;
use App\Country;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function userLogin(){
        // return view('users.login_register');
        $meta_title = "User Login-E-SMShopping Website";
        return view('users.login')->with(compact('meta_title'));
    }
    public function userRegister(){
        $countries = Country::get();
        $meta_title = "User Register-E-SMShopping Website";
        return view('users.register')->with(compact('countries','meta_title'));
    }
    public function register(Request $request){
        if($request->isMethod('post')){
           $data = $request->all();
        //    echo "<pre>";print_r($data);die;
            //CHECK IF USER ALREADY EXISTS
            $userCount = User::where('email',$data['email'])->count();
            if($userCount>0){
                return redirect()->back()->with('flash_message_error','ສະໝັກສະມາຊິກບໍ່ສໍາເລັດ ເນື່ອງຈາກທ່ານເປັນສະມາຊິກໃນລະບົບແລ້ວ!!');
            }else{
                $user = new User;
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = bcrypt($data['password']);
                $user->address = $data['user_address'];
                $user->city = $data['user_city'];
                $user->state = $data['user_state'];
                $user->country = $data['user_country'];
                $user->pincode = $data['user_pincode'];
                $user->mobile = $data['user_mobile'];                
                if(empty($user->address) || empty($user->city) || empty($user->state) || empty($user->country) || empty($user->pincode) || empty($user->mobile)){
                    $user->address = '';
                    $user->city = '';
                    $user->state = '';
                    $user->country = '';
                    $user->pincode = '';
                    $user->mobile = '';
                }
                $user->admin = 0;
                $user->save();

                //ສົ່ງຂໍ້ຄວາມຫາອີເມວຈາກ sinkerd99@gmail.com -> to register email
                /*$email = $data['email'];
                $messageData = ['email'=>$data['email'],'name'=>$data['name']];
                Mail::send('emails.registerMail',$messageData,function($message)use($email){
                    $message->to($email)->subject('Registration with E-com Website');
                });*/

                //ຢືຶນຢັນອີເມວ
                $email = $data['email'];
                $messageData = ['email'=>$data['email'],'name'=>$data['name'],'code'=>base64_encode($data['email'])];
                Mail::send('emails.confirmation',$messageData,function($message)use($email){
                    $message->to($email)->subject('ກະລຸນາຢືນຢັນບັນຊີຂອງທ່ານ');
                });

                return redirect(url('/user-Login'))->with('flash_message_success','ສະໝັກສະມາຊິກສໍາເລັດແລ້ວ!! ກະລຸນາຢືນຢັນບັນຊີຂອງທ່ານທາງອີເມວ ເພື່ອເຂົ້າສູ່ລະບົບ');

                // if(Auth::attempt(['email'=>$data['email'],'password'=>$data['password']])){
                // //     Session::put('frontSession',$data['email']);
                // return redirect(url('/user-Login'))->with('flash_message_success','ສະໝັກສະມາຊິກສໍາເລັດແລ້ວ!!');
                // }
               
            }
        }
    }
    public function checkEmail(Request $request){
            $data = $request->all();
            $userCount = User::where('email',$data['email'])->count();
            if($userCount>0){
                echo "false";
            }else{
                echo "true";die;
            }
    }
    public function confirmAccount($email){
        $email = base64_decode($email);
        $userCount = User::where('email',$email)->count();
        if($userCount > 0){
            $userDetails = User::where('email',$email)->first();
            if($userDetails->status == 1){
                return redirect('/user-Login')->with('flash_message_success','ບັນຊີຂອງທ່ານຢຶນຢັນຮຽບຮ້ອຍແລ້ວ!! ຕອນນີ້ທ່ານສາມາດເຂົ້າສຸ່ລະບົບໄດ້ແລ້ວ');
            }else{
                User::where('email',$email)->update(['status'=>'1']);

                //ສົ່ງຂໍ້ຄວາມຍິນດີຕ້ອນຮັບ
                $messageData = ['email'=>$email,'name'=>$userDetails->name];
                Mail::send('emails.welcome',$messageData,function($message)use($email){
                    $message->to($email)->subject('ຍິນດີຕ້ອນຮັບສູ່ເວັບໄຊ SMSHOPPING');
                });

                return redirect('/user-Login')->with('flash_message_success','ບັນຊີຂອງທ່ານຍັງຢຶນຢັນຮຽບຮ້ອຍແລ້ວ!! ຕອນນີ້ທ່ານສາມາດເຂົ້າສຸ່ລະບົບໄດ້ແລ້ວ');
            }
        }else{
            abort(404);
        }
    }
    public function login(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            if(Auth::attempt(['email'=>$data['email'],'password'=>$data['password']])){
                $userStatus =User::where('email',$data['email'])->first();
                if($userStatus->status==0){
                    return redirect()->back()->with('flash_message_error','ບັນຊີຂອງທ່ານຍັງບໍ່ໄດ້ຢຶນຢັນ ເພື່ອເຂົ້າສູ່ລະບົບ!! ກະລຸນາຢືນຢັນຜ່ານລີ້ງທີ່ເວັບໄຊສົ່ງໄປຍັງອີເມວທີ່ໃຊ້ສະໝັກ');
                    // return redirect()->back()->with('flash_message_error','ບັນຊີຂອງທ່ານຖືກລະງັບການໃຊ້ງານເພື່ອຄວາມປອດໄພ ກະລຸນາຕິດຕໍ່ທາງເວັບໄຊເພື່ອນໍາໃຊ້ບັນຊີ');
                }
                Session::put('frontSession',$data['email']);
                //ADD TO CART FUNCITON IF NOT LOGIN BUT ADD TO CART SO IF YOU LOGIN PRODUCT INCART WILL COME
                if(!empty(Session::get('session_id'))){
                    $session_id = Session::get('session_id');
                    Cart::where('session_id',$session_id)->update(['user_email' => $data['email']]);
                }
                return redirect('/cart');
                //Session::put('adminSession',$data['email']);
            }
            else{
                return redirect()->back()->with('flash_message_error','ອີເມວ ຫຼື ລະຫັດຜ່ານ ຂອງທ່ານບໍ່ຖືກຕ້ອງ');
            }
        }
    }
    public function account(Request $request){
        $user_id = Auth::user()->id;
        $userDetails = User::find($user_id);
        $userDetails = json_decode(json_encode($userDetails));
        // echo "<pre>";print_r($userDetails);die;
        if($request->isMethod('post')){
            $data = $request->all();
            if(empty($data['state'])){
                $data['address'] = '';
            }
            if(empty($data['address'])){
                $data['address'] = '';
            }
            if(empty($data['city'])){
                $data['city'] = '';
            }
            if(empty($data['name'])){
                return redirect()->back()->with('flash_message_error','ກາລຸນາປ້ອນຊື່ຜູ່ໃຊ້!!');
            }else{
                $mobile = is_numeric ($data['mobile']) ? true : false;
                if($mobile==true){
                    $user = User::find($user_id);
                    $user->name = $data['name'];
                    $user->address = $data['address'];
                    $user->city = $data['city'];
                    $user->state = $data['state'];
                    $user->country = $data['country'];
                    $user->pincode = $data['pincode'];
                    $user->mobile = $data['mobile'];
                    $user->save();
                    return redirect()->back()->with('flash_message_success','ທ່ານອັບເດດບັນຊີສໍາເລັດແລ້ວ!!');
                }else{
                    return redirect()->back()->with('flash_message_error','ກະລຸນາປ້ອນເບີໂທລະສັບໃຫ້ຖືກຕ້ອງ!!');
                }
            }
        }
        $countries = Country::get();
        return view('users.account')->with(compact("countries","userDetails"));
    }
    public function logout(){
        Auth::logout();
        Session::forget('frontSession');
        Session::forget('session_id');
        return redirect('/');
    }
    public function chkUserPassword(Request $request){
        $data = $request->all();
        $current_password = $data['current_pwd'];
        $user_id = Auth::User()->id;
        $check_password = User::where('id',$user_id)->first();
        if(Hash::check($current_password,$check_password->password)){
            echo "true"; die;
        }else{
            echo "false"; die;
        }
    }
    public function updatePassword(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $old_pwd = User::where('id',Auth::User()->id)->first();
            $current_pwd = $data['current_pwd'];
            if(Hash::check($current_pwd,$old_pwd->password)){
                //Update Password
                $new_pwd = bcrypt($data['new_pwd']);
                User::where('id',Auth::user()->id)->update(['password'=>$new_pwd]);
                return redirect()->back()->with('flash_message_success','ອັບເດດລະຫັດຜ່ານສຳເລັດຮຽບຮ້ອຍແລ້ວ!!');
            }
                return redirect()->back()->with('flash_message_error','ລະຫັດຜ່ານປະຈຸບັນທີ່ທ່ານປ້ອນບໍ່ຖືກຕ້ອງ!!');
        }
    }
    public function viewUsers(){
        $users = User::get();
        $users = json_decode(json_encode($users));
        // echo "<pre>";print_r($users);die;
        return view('admin.users.view_users')->with(compact('users'));
    }
    public function forgotPassword(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>";print_r($data);die;

            // For check Email Access or not
            $userCount = User::where('email',$data['email'])->count();
            if($userCount==0){
                return redirect()->back()->with('flash_message_error','ອີເມວຂອງທ່ານບໍ່ມີໃນລະບົບ !! ກະລຸນາລົງທະບຽນ ແລ້ວລອງໃໝ່ອີກຄັ້ງ');
            }
            //Get user Details
            $userDetails = User::where('email',$data['email'])->first();

            //ສ້າງລະຫັດຜ່ານແບບແລນດ່ອມ
            $random_password = str_random(8); 

            //ສ້າງລະັດຜ່ານໃໝ່
            $new_password = bcrypt($random_password);

            //ອັບເດດລະຫັດຜ່ານ
            User::where('email',$data['email'])->update(['password'=>$new_password]);

            //ສົ່ງລະຫັດຜ່ານໃຫ້ທາງອິີເມວ
            $email = $data['email'];
            $name = $userDetails->name;
            $messageData = [
                'email'=>$email,
                'name'=>$name,
                'password'=>$random_password
            ];
            Mail::send('emails.forgotpassword',$messageData,function($message) use ($email){
                $message->to($email)->subject('ລະຫັດຜ່ານໃໝ່ຂອງທ່ານ ເວັບໄຊ-SMShopping');
            });
             return redirect()->back()->with('flash_message_success','ລະຫັດຜ່ານຂອງທ່ານຖືກອັບເດດຮຽບຮ້ອຍແລ້ວ !! ກະລຸນາ ກວດສອບລະຫັດຜ່ານໃໝ່ຂອງທ່ານທາງອີເມວ');
        }
        return view('users.forgot_password');
    }
}

