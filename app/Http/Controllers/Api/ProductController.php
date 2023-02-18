<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\DeleteRequest;
use App\Http\Requests\Api\Product\StoreRequest;
use App\Http\Requests\Api\Product\UpdateRequest;
use App\Models\Product;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newInstance();
            $product->fill($inputs);
            $product->user_id = Auth::id();
            if (!empty($inputs['image'])) {
                $this->uploadBase64Image($request, $product);
            }
            if (!$product->save()) {
                DB::rollback();
                return $this->error(__('product.add'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('product.added'), $product);
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
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            $product->fill($inputs);
            if (!empty($inputs['image'])) {
                $this->uploadBase64Image($request, $product);
            }
            if (!$product->save()) {
                DB::rollback();
                return $this->error(__('product.update'), ERROR_400);
            }
            DB::commit();
            return $this->successWithData(__('product.updated'), $product);
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
            $query = $this->product->newQuery()->where('user_id', Auth::id());
            if (!empty($inputs['search'])) {
                $query->where(function ($q) use ($inputs) {
                    $this->search($q, $inputs['search'], ['name', 'model', 'specification']);
                    $this->search($q, $inputs['search'], ['name'], '');
                    $this->search($q, $inputs['search'], ['name'], 'subcategory');
                });
            }
            $products = $query->paginate(PAGINATE);
            return $this->successWithData(__('product.fetched'), $products);
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
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            if (!$product->delete()) {
                DB::rollback();
                return $this->error(__('product.delete'), ERROR_400);
            }
            DB::commit();
            return $this->success(__('product.deleted'));
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
