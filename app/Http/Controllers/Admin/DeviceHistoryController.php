<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceHistory;
use Illuminate\Http\Request;

class DeviceHistoryController extends Controller
{
    private $deviceHistory;
    public function __construct()
    {
        $this->deviceHistory = new DeviceHistory();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->deviceHistory->newQuery()->orderBy('created_at', 'DESC')->with(['user']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['name', 'topic', 'mac_address']);
                searchTable($q, $inputs['search'], ['first_name', 'last_name'], 'user');
            });
        }
        $device_histories = $query->paginate(PAGINATE);
        return view('admin.device_histories.listing', compact('device_histories'));
    }
}
