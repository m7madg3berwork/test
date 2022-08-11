<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\DeliveryBoy;
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
        /**
         * Check if user found
         */
        if (
            User::where('phone', $request->email_or_phone)
            ->where("user_type", $request->user_type)
            ->first() != null
        ) {
            return $this->returnError('404', translate('User already exists.'));
        }

        /**
         * Create User
         */
        if ($request->user_type == 'delivery_boy') {
            $user = User::create([
                'name'                   => $request->name,
                'phone'                  => $request->email_or_phone,
                'user_type'              => $request->user_type,
                'active'                 => 0,
                'delivery_type'          => $request->delivery_type,
                'zone_id'                => $request->zone_id,
                'national_id'            => $request->national_id,
                'national_id_attachment' => $request->national_id_attachment,
                'national_id_expired'    => $request->national_id_expired,
                'license_id'             => $request->license_id,
                'license_id_attachment'  => $request->license_id_attachment,
                'license_id_expired'     => $request->license_id_expired,
                'license_car'            => $request->license_car,
                'license_car_attachment' => $request->license_car_attachment,
                'license_car_expired'    => $request->license_car_expired
            ]);
            DeliveryBoy::create([
                'user_id' => $user->id
            ]);
        } else {
            // customer
            if ($request->customer_type == 'retail') {

                $code = generateOTPCode();
                sendOTPMessage($request->email_or_phone, $code);

                $user = User::create([
                    'name'              => $request->name,
                    'phone'             => $request->email_or_phone,
                    'user_type'         => $request->user_type,
                    'customer_type'     => $request->customer_type,
                    'active'            => 1,
                    'email_verified_at' => null,
                    'verification_code' => $code
                ]);
            } else {
                $user = User::create([
                    'name'                       => $request->name,
                    'phone'                      => $request->email_or_phone,
                    'user_type'                  => $request->user_type,
                    'customer_type'              => $request->customer_type,
                    'active'                     => 0,
                    'owner_name'                 => $request->owner_name,
                    'commercial_name'            => $request->commercial_name,
                    'commercial_registration_no' => $request->commercial_registration_no,
                    'commercial_registry'        => $request->commercial_registry,
                    'tax_number_certificate'     => $request->tax_number_certificate,
                    'tax_number'                 => $request->tax_number,
                    'city_id'                    => $request->city_id,
                ]);
            }
            Customer::create([
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Registration Successful. Please verify and log in to your account.'),
            'user_id' => $user->id
        ]);
    }



    public function login(Request $request)
    {
        $validate = Validator($request->all(), [
            'phone'     => 'required',
            'user_type' => 'required'
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnSValidationError($code, $validate);
        }

        $user = User::where('phone', $request->phone)
            ->where("user_type", $request->user_type)
            ->first();
        if (!$user) {
            return $this->returnError('404', translate('User not exists.'));
        } else {
            if ($user->active == 0) {
                return $this->returnError('404', translate('User not active wait approve from admin.'));
            }
        }

        $code = generateOTPCode();
        sendOTPMessage($request->phone, $code);

        $user->email_verified_at = null;
        $user->verification_code = $code;
        $user->save();

        return response()->json([
            'result'  => true,
            'message' => translate('Please verify your account to log in to your account.'),
            'user_id' => $user->id
        ]);
    }

    public function resendCode(Request $request)
    {
        $validate = Validator($request->all(), [
            'register_by' => 'required|in:phone,email',
            'user_id' => 'required',
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $code = generateOTPCode();
        $ret = '';

        $user = User::where('id', $request->user_id)->first();

        if (!$user) {
            return $this->returnError('404', translate('User not exists.'));
        }

        $user->verification_code = $code;

        if ($request->register_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {

            sendOTPMessage($user->phone, $code);

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
        } else {
            if ($user->active == 0) {
                return $this->returnError('404', translate('User not active wait approve from admin.'));
            }
        }

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return $this->loginSuccess($user);
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