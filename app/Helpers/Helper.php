<?php

use App\Models\CommandLog;
use Illuminate\Support\Facades\DB;

function getTimeList()
{

}

function createUniqueId($model, $type = 'GR')
{
    do {
        $uid = $type . '_' . substr(strtoupper(dechex(abs(rand() * 9e6 * microtime(true)))), 0, 6);
        $found = $model->where('uid', $uid)->exists();
    } while ($found);
    DB::table($model->getTable())->whereId($model->id)->update(['uid' => $uid]);
}

function generateVerificationCode($table = 'users', $col = 'verification_code')
{
    do {
        $verficationCode = rand(111111, 999999);
    } while (DB::table($table)->where($col, $verficationCode)->exists());
    return $verficationCode;
}

function updateCommandLog($msg, $topic, $mqtt)
{
    $response = json_decode($msg, TRUE);
    if (!empty($response['status']) && !empty($response['commandID']) && !empty($response['timestamp'])) {
        if ($command = CommandLog::where('command_id', $response['commandID'])->where('timestamp', (string) $response['timestamp'])->where('topic', $topic)->first()) {
            $command->response = $msg;
            $command->status = true;
            $command->save();
            Log::info("Log Id => " . $command->id . ", Command Updated");
        } else Log::info("Command Id Not Found");
    } else Log::info('Parameters missing  => ' .  json_encode($msg));
    $mqtt->interrupt();
}


function searchTable($query, $keyword, $filters, $with = null)
{
    if ($with) {
        $query->orWhereHas($with, function ($q) use ($filters, $keyword) {
            foreach ($filters as $key => $column) {
                if ($key == 0) {
                    $q->where($column, 'LIKE', '%' . $keyword . '%');
                } else {
                    $q->orWhere($column, 'LIKE', '%' . $keyword . '%');
                }
            }
        });
    } else {
        foreach ($filters as $key => $column) {
            $query->orWhere($column, 'LIKE', '%' . $keyword . '%');
        }
    }

    return $query;
}

function isRouteForSearch($route)
{
    $routeArray = explode(".", $route);
    $lastElement = end($routeArray);
    $validRoutes = [
        'requests',
        'listing',
        'public_requests',
        'listingDalua'
    ];
    if(in_array($lastElement, $validRoutes))
    {
        return true;
    }
    return false;
}
