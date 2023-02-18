<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Aquarium\ChangeStatusRequest;
use App\Http\Requests\Api\Aquarium\DeleteParameterRequest;
use App\Http\Requests\Api\Aquarium\DeleteRequest;
use App\Http\Requests\Api\Aquarium\DetailRequest;
use App\Http\Requests\Api\Aquarium\ListParameterRequest;
use App\Http\Requests\Api\Aquarium\ParametersDetailRequest;
use App\Http\Requests\Api\Aquarium\SharedUsersRequest;
use App\Http\Requests\Api\Aquarium\StoreParametersRequest;
use App\Http\Requests\Api\Aquarium\StoreRequest;
use App\Http\Requests\Api\Aquarium\UpdateParameterRequest;
use App\Http\Requests\Api\Aquarium\UpdateRequest;
use App\Jobs\PushNotificationJob;
use App\Models\Aquarium;
use App\Models\AquariumParameter;
use App\Models\Device;
use App\Models\Group;
use App\Models\User;
use App\Models\UserAquarium;
use App\Traits\Api\AquariumParameterTrait;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AquariumController extends Controller
{
    private $aquarium, $aquariumParameter, $device, $group, $user, $userAquarium;

    use AquariumParameterTrait;

    public function __construct()
    {
        $this->aquarium = new Aquarium();
        $this->aquariumParameter = new AquariumParameter();
        $this->userAquarium = new UserAquarium();
        $this->device = new Device();
        $this->user = new User();
        $this->group = new Group();
    }

    public function store(StoreRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquarium = $this->aquarium->newInstance();
            $aquarium->fill($inputs);
            $aquarium->user_id = Auth::id();
            if (!$aquarium->save()) {
                DB::rollback();
                return $this->error(__('aquarium.add'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('aquarium.added'), $aquarium->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function update(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquarium = $this->aquarium->newQuery()->where('id', $inputs['id'])->first();
            $aquarium->fill($inputs);
            if (!$aquarium->save()) {
                DB::rollback();
                return $this->error(__('aquarium.update'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('aquarium.updated'), $aquarium);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function listing(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $data = [];
            $query = $this->aquarium->newQuery()->where('user_id', Auth::id())->withCount('groups', 'devices');
            if (!empty($inputs['search'])) {
                $query->where(function ($query) use ($inputs) {
                    $this->search($query, $inputs['search'], ['name', 'temperature', 'ph', 'salinity', 'alkalinity', 'magnesium', 'nitrate', 'phosphate']);
                });
            }
            $res = $query->get();
            foreach ($res as $key => $aquarium) {
                $aquarium->users = $this->user->newQuery()->where(function ($q) use ($aquarium) {
                    $q->whereHas('sharedDevices', function ($q) use ($aquarium) {
                        $q->where('aquarium_id', $aquarium->id);
                    })->orWhereHas('sharedGroups', function ($q) use ($aquarium) {
                        $q->where('aquarium_id', $aquarium->id);
                    })->orWhereHas('sharedAquaria', function ($q) use ($aquarium) {
                        $q->where('aquarium_id', $aquarium->id);
                    });
                })->get();
            }
            $data['aquariums'] = $res;


            // Shared Aquariums

            $query = $this->aquarium->newQuery()->where(function ($q) {
                $q->whereHas('devices', function ($q) {
                    $q->whereHas('users', function ($q) {
                        $q->where('user_devices.user_id', Auth::id());
                    });
                })->orWhereHas('groups', function ($q) {
                    $q->whereHas('users', function ($q) {
                        $q->where('user_groups.user_id', Auth::id());
                    });
                });
            })->withCount([
                'devices' => function ($q) {
                    $q->whereHas('users', function ($q) {
                        $q->where('user_devices.user_id', Auth::id());
                    });
                },
                'groups' => function ($q) {
                    $q->whereHas('users', function ($q) {
                        $q->where('user_groups.user_id', Auth::id());
                    });
                }
            ])->with(['user']);
            if (!empty($inputs['search'])) {
                $query->where(function ($query) use ($inputs) {
                    $this->search($query, $inputs['search'], ['name', 'temperature', 'ph', 'salinity', 'alkalinity', 'magnesium', 'nitrate', 'phosphate']);
                });
            }
            $data['shared'] = $query->get();
            $shared_aquariums = $this->aquarium->newQuery()
                ->whereHas('userAquariums')
                ->where('user_id', Auth::id())
                ->with(['users'])
                ->withCount('groups', 'devices')
                ->get();
            $shared_aquariums_user = $this->aquarium->newQuery()
                ->where('user_id', '!=', Auth::id())
                ->whereHas('userAquariums', function($q){
                    $q->whereUserId(Auth::id());
                })
                ->with(['users' => function($q){
                    $q->where('users.id', Auth::id());
                }, 'user'])
                ->withCount('groups', 'devices')
                ->get();
            $data['shared_aquariums'] = $shared_aquariums;
            $data['shared_aquariums_user'] = $shared_aquariums_user;
            return $this->successWithData(__('aquarium.fetched'), $data);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function details(DetailRequest $request)
    {
        $details = $this->aquarium->newQuery()->where('id', $request->id)
            ->with([
                'groups' => function ($q) {
                    $q->with('user')->withCount('devices');
                },
                'devices' => function ($q) {
                    $q->where('group_id', NULL)->with(['user', 'product']);
                }
            ])->first();
        return $this->successWithData(__('aquarium.details'), $details);
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquarium = $this->aquarium->newQuery()->where('id', $inputs['id'])->first();
            if ($aquarium->devices->count() || $aquarium->groups->count()) {
                DB::rollback();
                return $this->error(__('aquarium.hasDevices'), ERROR_400);
            }
            if (!$aquarium->delete()) {
                DB::rollback();
                return $this->error(__('aquarium.delete'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('aquarium.deleted'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function sharedUsers(SharedUsersRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $users = $this->user->newQuery()
                ->where('id', '!=', Auth::id())
                ->whereHas('userAquariums', function($q) use($inputs){
                    $q->whereAquariumId($inputs['aquarium_id']);
                })->paginate(PAGINATE);
            DB::commit();
            return $this->successWithData(__('user.fetched'), $users);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function storeParameters(StoreParametersRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquariumParameter = $this->aquariumParameter->newInstance();
            $aquariumParameter->fill($inputs);
            $aquariumParameter->user_id = Auth::id();
            if (!$aquariumParameter->save()) {
                DB::rollback();
                return $this->error(__('aquarium.parameterAdd'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('aquarium.parameterAdded'), $aquariumParameter->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function listParameters(ListParameterRequest $request)
    {
        $inputs = $request->all();
        $parameters = $this->aquariumParameter->newQuery()->where('aquarium_id', $inputs['aquarium_id'])->whereUserId(Auth::id())->get();
        return $this->successWithData(__('aquarium.parameterFetched'), $parameters);
    }


    public function updateParameters(UpdateParameterRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquariumParameter = $this->aquariumParameter->newQuery()->where('id', $inputs['id'])->first();
            $aquariumParameter->fill($inputs);
            if (!$aquariumParameter->save()) {
                DB::rollback();
                return $this->error(__('aquarium.parameterUpdate'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('aquarium.parameterUpdated'), $aquariumParameter->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function deleteParameters(DeleteParameterRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquariumParameter = $this->aquariumParameter->newQuery()->where('id', $inputs['id'])->first();
            if (!$aquariumParameter->delete()) {
                DB::rollback();
                return $this->error(__('aquarium.parameterDelete'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('aquarium.parameterDeleted'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function parametersDetail(ParametersDetailRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = [];
            $inputs = $request->all();
            $data[$inputs['parameter']] = $this->getParameterFormattedDetail($inputs['parameter']);

            DB::commit();
            return $this->successWithData(__('aquarium.parameterFetched'), $data);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function approveAquarium(ChangeStatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user_aq = $this->userAquarium->newQuery()->whereId($inputs['id'])->first();
            if($inputs['status'] == 2)
            {
                if(!$user_aq->delete())
                {
                    return $this->error('Operation failed', ERROR_400);
                }
            }else{
                $user_aq->status = $inputs['status'];
                if(!$user_aq->save())
                {
                    return $this->error('Operation failed', ERROR_400);
                }

                // $aquarium = $this->aquarium->newQuery()->whereId($user_aq->aquarium_id)->first();
                // $data['type'] = "acceptAquarium";
                // $data['aquarium_name'] = $aquarium->name;
                // $data['username'] = Auth::user()->first_name;
                // $data['user'] = $this->user->newQuery()->whereId($aquarium->user_id)->first();
                // dispatch(new PushNotificationJob($data));
            }

            DB::commit();
            return $this->success('Status Updated Successfully');
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }

    }

}
