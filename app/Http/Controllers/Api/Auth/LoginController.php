<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgetPasswordMailRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\LogoutRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifyResetCodeRequest;
use App\Jobs\SendMailJob;
use App\Models\NotificationDevice;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;


class LoginController extends Controller
{
    private $user, $notificationDevice;
    public function __construct()
    {
        $this->user = new User();
        $this->notificationDevice = new NotificationDevice();
    }

    public function login(LoginRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($user  = $this->user->newQuery()->where('username', $inputs['username'])->orWhere('email', $inputs['username'])->first()) {
                if (Hash::check($inputs['password'], $user->password)) {
                    if ($user->status == STATUS_DEACTIVE) {
                        DB::rollback();
                        return $this->error(__('auth.deactive'), ERROR_400);
                    }
                    if (!$user->email_verified_at) {
                        $user->verification_code = generateVerificationCode();
                        dispatch(new SendMailJob($user->email, 'Email Verification', ['verificationCode' => $user->verification_code], 'emails.email-verification'));
                        if ($user->save()) {
                            DB::commit();
                            return $this->successWithData(__('auth.notVerified'), ['email' => $user->email]);
                        }
                    }

                    Auth::login($user);
                    $this->user = Auth::user();
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
                    return $this->successWithData(__('auth.loggedIn'), $toReturn);
                }
            }
            DB::rollback();
            return $this->error(__('auth.invalidCredentials'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function forgetPasswordMail(ForgetPasswordMailRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($this->user->newQuery()->where('email', $inputs['email'])->exists()) {
                DB::table('password_resets')->where('email', $inputs['email'])->delete();
                $code = generateVerificationCode('password_resets', 'token');
                DB::table('password_resets')->insert([
                    [
                        'email' => $inputs['email'],
                        'token' => $code,
                        'created_at' => Carbon::now()
                    ]
                ]);
                dispatch(new SendMailJob($inputs['email'], 'Reset Password', ['verification_code' => $code], 'emails.forgot-password'));
            }
            DB::commit();
            return $this->success(__('auth.emailVerificationCode', ['email' => $inputs['email']]));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function verifyResetCode(VerifyResetCodeRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($verify = DB::table('password_resets')->where('email', $inputs['email'])->where('token', $inputs['verification_code'])->first()) {
                if (strtotime('-5 minutes') > strtotime($verify->created_at)) {
                    DB::rollback();
                    return $this->error(__('auth.verificationCodeExpired'), ERROR_400);
                } else {
                    $user = $this->user->where('email', $inputs['email'])->first();
                    Auth::login($user);
                    $this->user = Auth::user();
                    $this->user->jwt_sign = null;
                    $toReturn['user'] = Auth::user();
                    $toReturn['token'] = $this->user->generateJWTToken();
                    DB::commit();
                    return $this->successWithData(__('auth.codeVerified'), $toReturn);
                }
            }
            DB::commit();
            return $this->error(__('auth.invalidVerificationCode'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if ($verify = DB::table('password_resets')->where('email', $inputs['email'])->where('token', $inputs['verification_code'])->first()) {
                if (strtotime('-5 minutes') > strtotime($verify->created_at)) {
                    DB::rollback();
                    return $this->error(__('auth.verificationCodeExpired'), ERROR_400);
                } else {
                    $user = $this->user->newQuery()->where('email', $inputs['email'])->first();
                    $user->password = Hash::make($inputs['password']);
                    if ($user->save()) {
                        if (DB::table('password_resets')->where('email', $inputs['email'])->where('token', $inputs['verification_code'])->delete()) {
                            $user = $this->user->where('email', $inputs['email'])->first();
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
                            return $this->successWithData(__('passwords.reset'), $toReturn);
                        }
                    }
                    DB::rollback();
                    return $this->error(__('passwords.errorReset'), ERROR_400);
                }
            }
            DB::rollBack();
            return $this->error(__('auth.invalidVerificationCode'), ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function logout(LogoutRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $notificationDevice = $this->notificationDevice->newQuery()->where('uuid', $inputs['uuid'])->first();
            if(!$notificationDevice)
            {
                DB::rollBack();
                return $this->error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if(!$notificationDevice->delete())
            {
                DB::rollBack();
                return $this->error(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            $token = $request->header('Authorization');
            if($token)
            {
                JWTAuth::invalidate($token);
            }
            DB::commit();
            return $this->success('Logged Out Succesfully');

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }

    }

}
