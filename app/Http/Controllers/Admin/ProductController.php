<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\DeleteRequest;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Models\Product;
use App\Models\User;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private $product;
    public function __construct()
    {
        $this->product = new Product();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->product->newQuery()->orderBy('id', 'DESC');
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name', 'slug']);
            });
        }
        $products = $query->paginate(PAGINATE);
        return view('admin.products.listing', compact('products'));
    }

    public function add()
    {
        return view("admin.products.add");
    }

    public function edit($id)
    {
        $product = $this->product->newQuery()->whereId($id)->first();
        return view("admin.products.edit", compact('product'));
    }

    public function store(StoreRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newInstance();
            $product->fill($inputs);
            $product->slug = strtolower(str_replace(' ', '_', $product->name));
            $product->user_id = auth()->user()->id;
            if($request->hasFile('image'))
            {
                $this->uploadFile(request('image'), $product, 'image', false, "product-images");
            }
            if ($product->save()) {
                DB::commit();
                return redirect()->route('admin.products.listing')->with('success', 'Saved Successfully');
            }
            DB::rollback();
            return redirect()->back()->with('error', 'Error while saving product.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->whereId($inputs['id'])->first();
            $product->slug = strtolower(str_replace(' ', '_', $product->name));
            $product->fill($inputs);
            if($request->hasFile('image'))
            {
                $this->deleteFile($product->image);
                $this->uploadFile(request('image'), $product, 'image', false, "product-images");
            }
            if ($product->save()) {
                DB::commit();
                return redirect()->route('admin.products.listing')->with('success', 'Updated Successfully');
            }
            DB::rollback();
            return redirect()->back()->with('error', 'Error while updating product.');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $product = $this->product->newQuery()->where('id', $inputs['id'])->first();
            if(isset($product->image) && $product->image != url('assets/img/default.png'))
            {
                $this->deleteFile($product->image);
            }
            if (!$product->delete()) {

                DB::rollback();
                return redirect()->back()->with('error', 'Error while deleting product.');
            }
            DB::commit();
            return redirect()->route('admin.products.listing')->with('success', 'Deleted Successfully');
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }

}
