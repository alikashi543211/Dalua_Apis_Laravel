<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandLog;
use Illuminate\Http\Request;

class CommandLogController extends Controller
{
    private $commandLog;

    public function __construct()
    {
        $this->commandLog = new CommandLog();
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->commandLog->newQuery()->orderBy('id', 'DESC');
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['topic', 'command_id', 'timestamp', 'response']);
                searchTable($q, $inputs['search'], ['name'], 'device');
                searchTable($q, $inputs['search'], ['name'], 'group');
            });
        }
        if(isset($inputs['group_id']))
        {
            if($inputs['group_id'] == '1')
            {
                $query->where('group_id', '!=', NULL);
            }else{
                $query->where('group_id', NULL);
            }
        }
        if(isset($inputs['status']))
        {
            $query->where('status', $inputs['status']);
        }
        $commands = $query->paginate(PAGINATE);
        return view('admin.commands.listing', compact('commands'));
    }
}
