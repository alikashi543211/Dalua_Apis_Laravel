<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Group\DeleteRequest;
use App\Http\Requests\Api\Group\DetailRequest;
use App\Http\Requests\Api\Group\ListingRequest;
use App\Http\Requests\Api\Group\StoreRequest;
use App\Http\Requests\Api\Group\UpdateRequest;
use App\Models\Aquarium;
use App\Models\Device;
use App\Models\Group;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    private $group, $device, $aquarium;

    public function __construct()
    {
        $this->group = new Group();
        $this->device = new Device();
        $this->aquarium = new Aquarium();
    }

    public function store(StoreRequest $request)
    {
        try {

            DB::beginTransaction();
            $inputs = $request->all();
            $group = $this->group->newInstance();
            $group->fill($inputs);
            $group->timezone = $inputs['timezone'];
            $group->created_by = Auth::id();
            $group->user_id = $this->aquarium->whereId($inputs['aquarium_id'])->value('user_id');
            if ($group->save()) {
                $group = $group->fresh();
                if (!empty($inputs['devices'])) {
                    $this->device->newQuery()->whereIn('id', $inputs['devices'])->update(['group_id' => $group->id]);
                }
                $group->topic = str_replace(' ', '', Str::lower(Auth::user()->first_name) . '-' . Auth::id()) . "/group/" . $group->uid;
                if ($group->save()) {
                    $this->createDefaultScedule(DEFAULT_SCHEDULE_GROUP, $group);
                    DB::commit();
                    return $this->successWithData(__('group.added'), $group->fresh());
                }
            }
            DB::rollback();
            return $this->error(__('group.add'), ERROR_400);
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
            $group = $this->group->newQuery()->where('id', $inputs['id'])->first();
            $group->fill($inputs);
            if (!empty($inputs['image'])) {
                $this->uploadBase64Image($request, $group);
            }
            if (!$group->save()) {
                DB::rollback();
                return $this->error(__('group.update'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('group.updated'), $group->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function listing(ListingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $aquarium_id = $inputs['aquarium_id'];
            $query = $this->group->newQuery()->where(function($q){
                $q->where('user_id', Auth::id())->orWhere(function($q){
                    $q->whereHas('aquarium', function($q) {
                        $q->whereHas('userAquariums', function($q){
                            $q->where('user_aquaria.aquarium_id', request('aquarium_id'))->where('user_aquaria.user_id', Auth::id())->where('user_aquaria.status', SHARED_AQUARIUM_STATUS_ACCEPTED);
                        });
                    });

                });
            })->where('aquarium_id', $inputs['aquarium_id'])->with(['devices' => function($q){
                $q->with(['user', 'product']);
            }, 'schedule']);

            if (!empty($inputs['search'])) {
                $query->where(function ($q) use ($inputs) {
                    $this->search($q, $inputs['search'], ['name']);
                    $this->search($q, $inputs['search'], ['device_id', 'topic', 'group_topic'], 'devices');
                });
            }
            $groups = $query->paginate(PAGINATE);
            return $this->successWithData(__('group.fetched'), $groups);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $group = $this->group->newQuery()->where('id', $inputs['id'])->first();
            if ($group->devices->count()) {
                DB::rollback();
                return $this->error(__('group.hasDevices'), ERROR_400);
            }
            if (!$group->delete()) {
                DB::rollback();
                return $this->error(__('group.delete'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('group.deleted'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function detail(DetailRequest $request)
    {
        $inputs = $request->all();
        $group = $this->group->newQuery()->whereId($inputs['id'])
            ->with(['devices' => function($q){
                $q->with(['user', 'product']);
            }, 'schedule'])
            ->first();
        return $this->successWithData(__('group.fetched'), $group);
    }
}
