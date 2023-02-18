<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Share\GetUsersRequest;
use App\Http\Requests\Api\Share\RemoveAquariumRequest;
use App\Http\Requests\Api\Share\ShareAquariumRequest;
use App\Http\Requests\Api\Share\ShareDeviceRequest;
use App\Http\Requests\Api\Share\ShareGroupRequest;
use App\Jobs\PushNotificationJob;
use App\Models\Aquarium;
use App\Models\Device;
use App\Models\Group;
use App\Models\User;
use App\Models\UserAquarium;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    private $device, $group, $user, $userAquarium;

    public function __construct()
    {
        $this->device = new Device();
        $this->aquarium = new Aquarium();
        $this->userAquarium = new UserAquarium();
        $this->group = new Group();
        $this->user = new User();
    }

    public function device(ShareDeviceRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $device = $this->device->newQuery()->where('id', $inputs['device_id'])->first();
            $device->users()->sync($inputs['users']);
            DB::commit();
            return $this->success(__('device.shared'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function aquarium(ShareAquariumRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if(empty($inputs['user_id']) && empty($inputs['email']))
            {
                DB::rollBack();
                return $this->error(__('aquarium.aquarium_parameter'), ERROR_500);
            }

            if(!empty($inputs['user_id']))
            {
                $userId = $inputs['user_id'];
                $recordExists = $this->userAquarium
                    ->newQuery()
                    ->whereAquariumId($inputs['aquarium_id'])
                    ->whereUserId($userId)->first();
            }elseif(!empty($inputs['email']))
            {
                $user = $this->user->newQuery()->whereEmail($inputs['email'])->first();
                $userId = $user->id;
                $recordExists = $this->userAquarium
                    ->newQuery()
                    ->whereAquariumId($inputs['aquarium_id'])
                    ->whereUserId($userId)->first();
            }
            if(!$recordExists)
            {
                $userAq = $this->userAquarium->newInstance();
                $userAq->fill($inputs);
                $userAq->user_id = $userId;
                if(!$userAq->save())
                {
                    DB::rollBack();
                    return $this->error(__('aquarium.share'), ERROR_500);
                }
            }
            // $aquarium = $this->aquarium->newQuery()->whereId($inputs['aquarium_id'])->first();
            // $data['type'] = "sharedAquarium";
            // $data['aquarium_name'] = $aquarium->name;
            // $data['username'] = Auth::user()->first_name;
            // $data['user'] = $this->user->newQuery()->whereId($userId)->first();
            // dispatch(new PushNotificationJob($data));

            DB::commit();
            return $this->success(__('aquarium.shared'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function removeAquarium(RemoveAquariumRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if(empty($inputs['user_id']) && empty($inputs['email']))
            {
                DB::rollBack();
                return $this->error(__('aquarium.aquarium_parameter'), ERROR_500);
            }

            if(!empty($inputs['user_id']))
            {
                $userId = $inputs['user_id'];
                $recordExists = $this->userAquarium
                    ->newQuery()
                    ->whereAquariumId($inputs['aquarium_id'])
                    ->whereUserId($userId)->exists();
            }elseif(!empty($inputs['email']))
            {
                $user = $this->user->newQuery()->whereEmail($inputs['email'])->first();
                $userId = $user->id;
                $recordExists = $this->userAquarium
                    ->newQuery()
                    ->whereAquariumId($inputs['aquarium_id'])
                    ->whereUserId($userId)->exists();
            }
            if($recordExists)
            {
                $sharedAquarium = $this->userAquarium->newQuery()->whereAquariumId($inputs['aquarium_id'])->whereUserId($userId)->first();
                if(!$sharedAquarium->delete())
                {
                    DB::rollBack();
                    return $this->error(__('aquarium.remove'), ERROR_500);
                }
            }
            DB::commit();
            return $this->success(__('aquarium.removed'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function group(ShareGroupRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $group = $this->group->newQuery()->where('id', $inputs['group_id'])->first();
            $group->users()->sync($inputs['users']);
            DB::commit();
            return $this->success(__('group.shared'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function getUsers(GetUsersRequest $request)
    {
        $inputs = $request->all();
        $query = $this->user->newQuery()
            ->where('id', '!=', Auth::id())
            ->where('role_id', USER_APP);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                $this->search($q, $inputs['search'], ['username', 'first_name', 'middle_name', 'last_name']);
            });
        }
        if(!empty($inputs['aquarium_id']))
        {
            $query->where(function($q) use ($inputs){
                $q->whereDoesntHave('userAquariums')->orWhereHas('userAquariums', function($q) use($inputs){
                    $q->where('aquarium_id', '!=', $inputs['aquarium_id']);
                });
            });
        }
        if(!empty($inputs['show_email']))
        {
            $query->select(['email']);
        }else{
            $query->with('userAquariums');
        }
        $users = $query->paginate(PAGINATE)->toArray();
        return $this->successWithData('Users fetched successfully', $users);
    }

}
