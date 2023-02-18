<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateImageRequest;
use App\Http\Requests\Api\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\Profile\UpdateRequest;
use App\Models\User;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function updateProfile(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(auth()->user()->id)->first();
            $user->fill($inputs);
            if($request->hasFile('image'))
            {
                $this->deleteFile($user->image);
                $this->uploadFile(request('image'), $user, 'image', false, 'user-'.auth()->user()->id."-profile-image");
            }
            if (!$user->save()) {
                DB::rollback();
                return $this->error(__('profile.update'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('profile.updated'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function updateNotificationStatus()
    {
        try {
            DB::beginTransaction();
            $user = $this->user->newQuery()->whereId(auth()->user()->id)->first();
            if($user->notification_status)
            {
                $user->notification_status = 0;
            }else{
                $user->notification_status = 1;
            }
            if (!$user->save()) {
                DB::rollback();
                return $this->error(__('profile.notification_status_update'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('profile.notification_status_updated'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(auth()->user()->id)->first();
            if (!Hash::check($inputs['old_password'], $user->password))
            {
                DB::rollback();
                return $this->error(__('profile.incorrect_old'), ERROR_400);
            }
            if (Hash::check($inputs['password'], $user->password))
            {
                DB::rollback();
                return $this->error(__('profile.same_old_and_new'), ERROR_400);
            }
            $user->password = Hash::make($inputs['password']);
            if (!$user->save()) {
                DB::rollback();
                return $this->error(__('profile.password_update'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('profile.password_updated'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function detail()
    {
        try {
            DB::beginTransaction();
            $user = $this->user->newQuery()->whereId(auth()->user()->id)->withCount(['schedules', 'sharedAquaria as shared_aquarium_count', 'aquaria as aquarium_count'])->first();
            DB::commit();
            return $this->successWithData(__('profile.fetched'), $user);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
