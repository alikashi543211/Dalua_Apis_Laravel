<?php

namespace App\Traits\Api;

use Illuminate\Support\Facades\Auth;

trait AquariumParameterTrait
{
    private function getParameterFormattedDetail($column)
    {
        $data = [];
        // $data['ph'][0]['value'] = 100;
        // $data['ph'][0]['date'] = '2022-4-4';

        // $data['ph'][1]['value'] = 100;
        // $data['ph'][1]['date'] = '2022-4-4';

        // $data['ph'][2]['value'] = 100;
        // $data['ph'][2]['date'] = '2022-4-4';
        // dd($data);
        $output = [];
        $list = $this->aquariumParameter->select($column, 'created_at')->whereUserId(Auth::id())->get();
        $i = 0;
        foreach($list as $key => $value)
        {
            $data[$i]['value'] = $value->$column;
            $data[$i]['date'] = $value->created_at->format('Y-m-d');
            $i++;
        }
        return $data;
    }
}
