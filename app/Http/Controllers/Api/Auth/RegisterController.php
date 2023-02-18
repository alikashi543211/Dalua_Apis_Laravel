<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\NewUserRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\VerificationCodeResendRequest;
use App\Http\Requests\Api\Auth\VerifyEmailVerificationCodeRequest;
use App\Jobs\SendMailJob;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($inputs['login_type'] != LOGIN_EMAIL) {
                if($inputs['login_type'] == LOGIN_APPLE)
                {
                    $user = $this->user->newQuery()->where('social_user_id', $inputs['social_user_id'])->first();
                }else{
                    $user = $this->user->newQuery()->where('social_user_id', $inputs['social_user_id'])->where('email', $inputs['email'])->first();
                }
                if($user)
                {
                    Auth::login($user);
                    $this->user = Auth::user();
                    $this->user->jwt_sign = null;
                    $toReturn['user'] = Auth::user();
                    $toReturn['token'] = $this->user->generateJWTToken();
                    DB::commit();
                    return $this->successWithData(__('auth.registration'), $toReturn);
                }
            }
            if (!empty($inputs['email']) && $this->user->newQuery()->where('email', $inputs['email'])->exists()) {
                return $this->error('The email has already been taken.', ERROR_400);
            }
            $user = $this->user->newInstance();
            $user->fill($inputs);
            $user->password = $inputs['login_type'] == LOGIN_EMAIL ? Hash::make($inputs['password']) : NULL;
            $user->role_id = USER_APP;
            if ($inputs['login_type'] == LOGIN_EMAIL) {
                $user->verification_code = generateVerificationCode();
            } else {
                $user->status = STATUS_ACTIVE;
            }
            if ($user->save()) {
                $user = $user->fresh();
                if ($inputs['login_type'] == LOGIN_EMAIL) {
                    dispatch(new SendMailJob($user->email, 'Email Verification', ['verificationCode' => $user->verification_code], 'emails.email-verification'));
                } else {
                    Auth::login($user);
                    $this->user = Auth::user();
                    $this->user->jwt_sign = null;
                    $toReturn['user'] = Auth::user();
                    $toReturn['token'] = $this->user->generateJWTToken();
                    DB::commit();
                    return $this->successWithData(__('auth.registration'), $toReturn);
                }
                DB::commit();
                return $this->success(__('auth.emailVerificationCode', ['email' => $user->email]));
            }
            DB::rollback();
            return $this->error(__('auth.registrationError'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function verifyEmailVerificationCode(VerifyEmailVerificationCodeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('email', $inputs['email'])->where('verification_code', $inputs['verification_code'])->first();
            if (strtotime('-5 minutes') < strtotime($user->updated_at)) {
                $user->verification_code = NULL;
                $user->email_verified_at = Carbon::now();
                if ($user->save()) {
                    Auth::login($user);
                    $this->user = Auth::user();
                    $this->user->jwt_sign = null;
                    $toReturn['user'] = Auth::user();
                    $toReturn['token'] = $this->user->generateJWTToken();

                    if(!empty($inputs['uuid']) && !empty($inputs['token']) && !empty($inputs['type']))
                    {
                        if (!$notificationDevice = $this->notificationDevice->newQuery()->where('uuid', $inputs['uuid'])->first()) {
                            $notificationDevice = $this->notificationDevice->newInstance();
                            $notificationDevice->user_id = Auth::id();
                        }
                        $notificationDevice->fill($inputs);
                        if (!$notificationDevice->save()) {
                            DB::rollBack();
                            return $this->error(GENERAL_ERROR_MESSAGE, ERROR_400);
                        }
                    }

                    DB::commit();
                    return $this->successWithData(__('auth.registration'), $toReturn);
                }
            }
            DB::rollback();
            return $this->error(__('auth.verificationCodeExpired'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
    public function verificationCodeResend(VerificationCodeResendRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->where('email', $inputs['email'])->first();
            if (!$user->email_verified_at) {
                $user->verification_code = generateVerificationCode();
                if ($user->save()) {
                    DB::commit();
                    dispatch(new SendMailJob($user->email, 'Email Verification', ['verificationCode' => $user->verification_code], 'emails.email-verification'));
                    return $this->success(__('auth.emailVerificationCode', ['email' => $user->email]));
                }
            } else return $this->error(__('auth.emailAreadyVerified'), ERROR_400);
            DB::rollback();
            return $this->error(__('auth.registrationError'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function newUser(NewUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if (!$user = $this->user->newQuery()->where('unique_id', $inputs['unique_id'])->first()) {
                $id = $this->user->newQuery()->latest()->first()->id + 1;
                $user = $this->user->newInstance();
                $user->first_name = "User";
                $user->last_name = $id;
                $user->username = 'user-' . $id;
                $user->email =  'user' . $id . '@getnada.com';
                $user->email_verified_at = Carbon::now();
                $user->password = Hash::make('user' . $id);
                $user->role_id = USER_APP;
                $user->login_type = LOGIN_EMAIL;
                $user->unique_id = $inputs['unique_id'];
            }

            if ($user->save()) {
                $user = $user->fresh();
                Auth::login($user);
                $this->user = Auth::user();
                $this->user->jwt_sign = null;
                $toReturn['user'] = Auth::user();
                $toReturn['token'] = $this->user->generateJWTToken();
                DB::commit();
                return $this->successWithData(__('auth.loggedIn'), $toReturn);
            }
            DB::rollback();
            return $this->error(__('user.add'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
