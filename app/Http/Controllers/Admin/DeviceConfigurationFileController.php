<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IotConfigFile\DeleteRequest;
use App\Models\IotDeviceFile;
use App\Models\Product;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceConfigurationFileController extends Controller
{
    private $deviceConfig, $product;

    public function __construct()
    {
        $this->deviceConfig = new IotDeviceFile();
        $this->product = new Product();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->deviceConfig->newQuery();
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name', 'version']);
            });
        }
        $deviceConfigs = $query->orderBy('created_at', 'DESC')->paginate(PAGINATE);
        return view('admin.iot-config-files.listing', compact('deviceConfigs'));
    }

    public function add()
    {
        $products = $this->product->newQuery()->get();
        return view('admin.iot-config-files.add', compact('products'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $config = $this->deviceConfig->newQuery()->orderBy('version', 'DESC')->first();
            $deviceConfig = $this->deviceConfig->newInstance();
            $deviceConfig->name = $request->file('file')->getClientOriginalName();
            $deviceConfig->version = $request->version;
            $deviceConfig->product_id = $request->product_id;
            if($request->hasFile('file'))
            {
                $this->uploadFile(request('file'), $deviceConfig, 'location', false, "iot-device-files");
            }
            if ($deviceConfig->save()) {
                DB::commit();
                return redirect()->route('admin.iotFile.listing')->with('success', 'Saved Successfully');
            }
            DB::rollback();
            return redirect()->back()->with('error', 'Error while uploading file');
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
            $deviceConfig = $this->deviceConfig->newQuery()->where('id', $inputs['id'])->first();
            if(isset($deviceConfig->location))
            {
                $this->deleteFile($deviceConfig->location);
            }
            if (!$deviceConfig->delete()) {
                DB::rollback();
                return redirect()->back()->with('error', 'Error while deleting Iot config file.');
            }
            DB::commit();
            return redirect()->route('admin.iotFile.listing')->with('success', 'Deleted Successfully');
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), ERROR_500);
        }
    }
}
