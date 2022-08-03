<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Models\BusinessSetting;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Hash;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\Storage;

use Image;


class AuthController extends Controller
{
    use GeneralTrait;

    public function signup(Request $request)
    {

        if ($request->customer_type == 'retail') {
            $validate = Validator($request->all(), [
                'name' => 'required|string',
                'email_or_phone' => 'required|string',
                'register_by' => 'required|string',
                'customer_type' => 'required|string'
            ]);
        } else {
            $validate = Validator($request->all(), [
                'name' => 'required|string',
                'email_or_phone' => 'required|string',
                'register_by' => 'required|string',
                'customer_type' => 'required|string',
                'owner_name' => 'required|string',
                'commercial_name' => 'required|string',
                'commercial_registration_no' => 'required',
                'city_id' => 'required|exists:zones,id',
                'commercial_registry' => 'required',
                'tax_number_certificate' => 'required',
                'long' => 'required',
                'lat' => 'required'
            ]);
        }


        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $code = rand(1111, 9999);

        $ret = '';

        //return $ret;


        // return $request;


        if (User::where('email', $request->email_or_phone)->orWhere('phone', $request->email_or_phone)->first() != null) {
            return $this->returnError('404', translate('User already exists.'));
        }


        // $commercial_registry_name = auth()->user()->commercial_registry ?? null;
        // $tax_number_certificate_name = auth()->user()->tax_number_certificate ?? null;

        $name1 = auth()->user()->commercial_registry ?? null;
        $name2 = auth()->user()->tax_number_certificate_name ?? null;

        if ($request->customer_type == 'wholesale') {

            // return base64_encode(file_get_contents($request->file('tax_number_certificate')));

            // $name = time().'.' . explode('/', explode(':', substr( 'data:image/png;base64,'.base64_encode(file_get_contents($request->file('tax_number_certificate'))) , 0, strpos( 'data:image/png;base64,'.base64_encode(file_get_contents($request->file('tax_number_certificate'))) , ';')))[1])[1];

            $name1 = rand() . time() . '.' . explode('/', explode(':', substr($request->commercial_registry, 0, strpos($request->commercial_registry, ';')))[1])[1];

            Image::make($request->commercial_registry)->save(public_path('assets/img/commercial/') . $name1);

            // return $name;

            $name2 = rand() . time() . '.' . explode('/', explode(':', substr($request->tax_number_certificate, 0, strpos($request->tax_number_certificate, ';')))[1])[1];

            Image::make($request->tax_number_certificate)->save(public_path('assets/img/commercial/') . $name2);




            // if ($request->hasFile('commercial_registry')) {
            //     # Delete Old Image
            //     // if ($commercial_registry_name != null) {
            //     //     $this->deleteFile($commercial_registry_name, 'assets/img/commercial/');
            //     // }
            //     # Upload New Image & Return its New Name
            //     $image_name = $this->uploadImage($request->file('commercial_registry'), 'assets/img/commercial/');
            //     # Save New Name in DB
            //     $commercial_registry_name = $image_name;
            // }

            // if ($request->hasFile('tax_number_certificate')) {
            //     # Delete Old Image
            //     // if ($tax_number_certificate_name != null) {
            //     //     $this->deleteFile($tax_number_certificate_name, 'assets/img/commercial/');
            //     // }
            //     # Upload New Image & Return its New Name
            //     $image_name = $this->uploadImage($request->file('tax_number_certificate'), 'assets/img/commercial/');
            //     # Save New Name in DB
            //     $tax_number_certificate_name = $image_name;
            // }
        }

        // return response()->json([
        //     'name1' => $name1,
        //     'name2' => $name2,
        // ]);


        $user = new User([
            'name' => $request->name,
            'phone' => $request->email_or_phone,
            'password' => bcrypt($request->password),
            'customer_type' => $request->customer_type,
            'owner_name' => $request->owner_name,
            'commercial_name' => $request->commercial_name,
            'commercial_registration_no' => $request->commercial_registration_no,
            'city_id' => $request->city_id,
            'verification_code' => $code,
            'commercial_registry' => $name1,
            'tax_number_certificate' => $name2,
            'long' => $request->long ?? null,
            'lat' => $request->lat ?? null,
        ]);

        $user->save();
        // return $user;

        $user->email_verified_at = null;
        if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
            $user->email_verified_at = date('Y-m-d H:m:s');
        }

        if ($user->email_verified_at == null) {
            if ($request->register_by == 'email') {
                try {
                    $user->notify(new AppEmailVerificationNotification());
                } catch (\Exception $e) {
                }
            } else {

                try {
                    $userOTP = 'romooz';
                    $password = '102030';
                    $sendername = 'ROMOOZ';
                    //  $text = urlencode( $messageContent);
                    $text = $code;
                    $to = $request->email_or_phone;
                    // auth call
                    $url = "http://www.sms4ksa.com/api/sendsms.php?username=$userOTP&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=json";

                    $c = curl_init();
                    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($c, CURLOPT_URL, $url);
                    $contents = curl_exec($c);
                    curl_close($c);

                    if ($contents) $ret = $contents;
                    else return FALSE;

                    $ret = json_decode($ret, true);
                    // return $ret;
                    $ret['sent_code'] = $code;
                    if ($ret['Code'] == 100) {
                        $ret['status'] = true;
                    } else {
                        $ret['status'] = false;
                    }
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }

        // return $ret;
        if ($request->customer_type == 'retail') {
            $user->status = 'done';
        }


        $user->save();

        // return $user;

        //create token
        $user->createToken('tokens')->plainTextToken;

        return response()->json([
            'result' => true,
            'message' => translate('Registration Successful. Please verify and log in to your account.'),
            'user_id' => $user->id,
            'otp_ret' => $ret
        ], 201);
    }



    public function login(Request $request)
    {

        $validate = Validator($request->all(), [
            'phone' => 'required'
        ]);


        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $code = rand(1111, 9999);

        $ret = '';

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            if (!$user) {
                return $this->returnError('404', translate('User not exists.'));
            }
        }

        // $user->email_verified_at = null;
        // if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
        //     $user->email_verified_at = date('Y-m-d H:m:s');
        // }

        $userOTP = 'romooz';
        $password = '102030';
        $sendername = 'ROMOOZ';
        //  $text = urlencode( $messageContent);
        $text = $code;
        $to = $request->phone;
        // auth call
        $url = "http://www.sms4ksa.com/api/sendsms.php?username=$userOTP&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=json";

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) $ret = $contents;
        else return FALSE;

        $ret = json_decode($ret, true);
        // return $ret;
        $ret['sent_code'] = $code;
        if ($ret['Code'] == 100) {
            $ret['status'] = true;
        } else {
            $ret['status'] = false;
        }

        // if ($request->customer_type == 'retail') {
        //     $user->status = 'done';
        // }

        $user->email_verified_at = null;
        $user->verification_code = $code;
        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate('Please verify your account to log in to your account.'),
            'user_id' => $user->id,
            'otp_ret' => $ret
        ], 201);
    }

    public function resendCode(Request $request)
    {
        // return 'fffffff';

        $validate = Validator($request->all(), [
            'register_by' => 'required|in:phone,email',
            'user_id' => 'required',
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $code = rand(1111, 9999);
        $ret = '';

        $user = User::where('id', $request->user_id)->first();

        if (!$user) {
            return $this->returnError('404', translate('User not exists.'));
        }

        $user->verification_code = $code;

        if ($request->register_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {

            $userOTP = 'romooz';
            $password = '102030';
            $sendername = 'ROMOOZ';
            //  $text = urlencode( $messageContent);
            $text = $code;
            $to = $user->phone;
            // auth call
            $url = "http://www.sms4ksa.com/api/sendsms.php?username=$userOTP&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=json";

            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            $contents = curl_exec($c);
            curl_close($c);

            if ($contents) $ret = $contents;
            else return FALSE;

            $ret = json_decode($ret, true);
            // return $ret;
            $ret['sent_code'] = $code;
            if ($ret['Code'] == 100) {
                $ret['status'] = true;
            } else {
                $ret['status'] = false;
            }
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent again'),
            'otp_ret' => $ret
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        if (!$user) {
            return $this->returnError('404', translate('User not exists.'));
        }

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();

            // if($user->customer_type == 'retail'){
            return $this->loginSuccess($user);
            // }
            // return response()->json([
            //     'result' => true,
            //     'message' => translate('Your account is now verified.Please login'),
            // ], 200);

        } else {
            return $this->returnError('404', translate('Code does not match, you can request for resending the code'));
        }
    }

    // public function login(Request $request)
    // {
    //     /*$request->validate([
    //         'email' => 'required|string|email',
    //         'password' => 'required|string',
    //         'remember_me' => 'boolean'
    //     ]);*/

    //     $delivery_boy_condition = $request->has('user_type') && $request->user_type == 'delivery_boy';

    //     if ($delivery_boy_condition) {
    //         $user = User::whereIn('user_type', ['delivery_boy'])->where('email', $request->email)->orWhere('phone', $request->email)->first();
    //     } else {
    //         $user = User::whereIn('user_type', ['customer', 'seller'])->where('email', $request->email)->orWhere('phone', $request->email)->first();
    //     }

    //     if (!$delivery_boy_condition) {
    //         if (\App\Utility\PayhereUtility::create_wallet_reference($request->identity_matrix) == false) {
    //             return response()->json(['result' => false, 'message' => 'Identity matrix error', 'user' => null], 401);
    //         }
    //     }


    //     if ($user != null) {
    //         if (Hash::check($request->password, $user->password)) {

    //             if ($user->email_verified_at == null) {
    //                 return response()->json(['message' => translate('Please verify your account'), 'user' => null], 401);
    //             }
    //             return $this->loginSuccess($user);
    //         } else {
    //             return response()->json(['result' => false, 'message' => translate('Unauthorized'), 'user' => null], 401);
    //         }
    //     } else {
    //         return response()->json(['result' => false, 'message' => translate('User not found'), 'user' => null], 401);
    //     }
    // }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->returnSuccessMessage(translate('Successfully logged out'), '200');
    }

    public function socialLogin(Request $request)
    {
        if (!$request->provider) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found'),
                'user' => null
            ]);
        }

        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email'
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        $social_user_details = $social_user->userFromToken($request->access_token);

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        $existingUserByProviderId = User::where('provider_id', $request->provider)->first();

        if ($existingUserByProviderId) {
            return $this->loginSuccess($existingUserByProviderId);
        } else {
            $old_user = User::where('email', $request->email)->first();
            if ($old_user) {
                // return $this->returnError('203', translate('User already exist with this email'));
                return $this->loginSuccess($old_user);
            }
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'provider_id' => $request->provider,
                'email_verified_at' => Carbon::now()
            ]);
            $user->save();
        }
        return $this->loginSuccess($user);
    }

    protected function loginSuccess($user)
    {
        $token = $user->createToken('API Token')->plainTextToken;
        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => uploaded_asset($user->avatar_original),
                'phone' => $user->phone
            ]
        ]);
    }



    public function uploadImage($photo_name, $folder)
    {

        $image = $photo_name;
        // $request->filename;
        $realImage = base64_decode($image);

        $dir = public_path($folder);
        $full_path = "$dir/$photo_name->filename";

        $file_put = file_put_contents($full_path, $realImage); // int or false

        if ($file_put == false) {
            return response()->json([
                'result' => false,
                'message' => "File uploading error",
                'path' => "",
                'upload_id' => 0
            ]);
        }


        $extension = strtolower(File::extension($full_path));
        $size = File::size($full_path);

        if (!isset($type[$extension])) {
            unlink($full_path);
            return response()->json([
                'result' => false,
                'message' => "Only image can be uploaded",
                'path' => "",
                'upload_id' => 0
            ]);
        }


        // $upload->file_original_name = null;
        // $arr = explode('.', File::name($full_path));
        // for ($i = 0; $i < count($arr) - 1; $i++) {
        //     if ($i == 0) {
        //         $upload->file_original_name .= $arr[$i];
        //     } else {
        //         $upload->file_original_name .= "." . $arr[$i];
        //     }
        // }

        //unlink and upload again with new name
        unlink($full_path);
        $newFileName = rand(10000000000, 9999999999) . date("YmdHis") . "." . $extension;
        $newFullPath = "$dir/$newFileName";

        $file_put = file_put_contents($newFullPath, $realImage);

        if ($file_put == false) {
            return response()->json([
                'result' => false,
                'message' => "Uploading error",
                'path' => "",
                'upload_id' => 0
            ]);
        }

        $newPath = $newFileName;
        return $newPath;

        // $image = $photo_name;
        // $image_name = time() .''.$image->getClientOriginalName();
        // $destinationPath = public_path($folder);
        // $image->move($destinationPath, $image_name);
        // return $image_name;
    }

    public function deleteFile($photo_name, $folder)
    {
        $image_name = $photo_name;
        $image_path = public_path($folder) . $image_name;
        if (file_exists($image_path)) {
            @unlink($image_path);
        }
    }
}
