<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Category\DeleteRequest;
use App\Http\Requests\Api\Category\StoreRequest;
use App\Http\Requests\Api\Category\SubcategoryRequest;
use App\Http\Requests\Api\Category\UpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    private $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $category = $this->category->newInstance();
            $category->fill($inputs);
            $category->user_id = Auth::id();
            if (!$category->save()) {
                DB::rollback();
                return $this->error(__('category.add'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('category.added'), $category);
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
            $category = $this->category->newQuery()->where('id', $inputs['id'])->first();
            $category->fill($inputs);
            if (!$category->save()) {
                DB::rollback();
                return $this->error(__('category.update'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('category.updated'), $category);
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
            $query = $this->category->newQuery()->where('category_id', NULL)->where('user_id', Auth::id());
            if (!empty($inputs['search'])) {
                $this->search($query, $inputs['search'], ['name']);
            }
            $categories = $query->paginate(PAGINATE);
            return $this->successWithData(__('category.fetched'), $categories);
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
            $category = $this->category->newQuery()->where('id', $inputs['id'])->first();
            if (!$category->delete()) {
                DB::rollback();
                return $this->error(__('category.delete'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('category.deleted'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

    public function getSubcategories(SubcategoryRequest $request)
    {
        $inputs = $request->all();
        $query = $this->category->newQuery()->where('category_id', $inputs['id']);
        if (!empty($inputs['search'])) {
            $query->where(function ($query) use ($inputs) {
                $this->search($query, $inputs['search'], ['name']);
            });
        }
        $categories = $query->paginate(PAGINATE);
        return $this->successWithData(__('category.fetched'), $categories);
    }
}
