<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $filePath = 'uploads/', $imageName = 'image';
    public function error($message, $code)
    {
        return response()->json([
            'success' => false,
            'message' => [$message]
        ], $code);
    }

    public function success($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ], SUCCESS_200);
    }

    public function successWithData($message, $data)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], SUCCESS_200);
    }

    /**
     * Description: The following method is used to add searcg filter into query
     * @author Muhammad Abid - I2L
     * @param \Illuminate\Database\Eloquent\Query $query
     * @param Array $filters
     * @param String $with
     * @return \Illuminate\Database\Eloquent\Query
     */
    protected function search($query, $keyword, $filters, $with = null)
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

    protected function uploadBase64Image($input, $model)
    {
        // $this->deleteFile($model);
        if (is_array($input)) {
            $orig_input = $input[$this->imageName];
            $input = new \stdClass;
            $input->{$this->imageName} = $orig_input;
        }

        $mimeType = explode(':', $input->{$this->imageName});
        if (count($mimeType) > 1) {
            // $model->base64 = $input->{$this->imageName};
            $mimeType = explode(';', $mimeType[1]);
            if (count($mimeType) > 1) {
                $imageBytes = explode(',', $mimeType[1]);
                if (count($imageBytes) > 1) {
                    return $this->attachBytesImage($imageBytes[1], $mimeType[0], $model);
                }
            }
        }
        return false;
    }

    /**
     * Description: the following method is used to convert Base64 to images and attach to the respective model instance or on relational instance
     * @author Muhammad Abid - DS
     * @param $input
     * @param $model
     * @param bool $isRelationUpload
     * @return bool
     */
    protected function attachBytesImage($imageBytes, $mimeType, $model)
    {
        // File Path
        $filePath = $this->filePath . date('Y') . '/' . date('m') . '/';
        //renaming the file
        $name = time() . '_' . rand(5000, 100000) . ".";
        $folderPath = public_path('/') . $filePath;
        // check the file is image or not

        $name = $name . explode('/', $mimeType)[1];
        file_put_contents($folderPath . $name, base64_decode($imageBytes));

        $model->{$this->imageName} = $filePath . $name;
        return $model;
    }

    protected function uploadFile($file, $model, $column, $v4 = false, $folderTitle = 'device-configurations' )
    {
        $type = $v4 ? '-v4' : '';
        $folderName = $folderTitle . $type;
        // make file path structure
        $filePath = $folderName . '/' . date('Y') . '/' . date('m') . '/';
        //Set public folder path
        //renaming the file
        $name = $column . '_' . time() . '_' . rand(5000, 100000) . "." . $file->getClientOriginalExtension();
        if (env('AWS_ENV')) {
            Storage::disk('s3')->putFileAs($filePath, $file, $name);
        } else {
            $folderPath = public_path('/') . $filePath;
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            if (!$file->move($folderPath, $name)) {
                return false;
            }
        }
        $model->{$column} = $filePath . $name;
        return true;
    }
    protected function uploadFileGraph($file, $model, $column, $v4 = false, $folderTitle = 'device-configurations' )
    {
        $type = $v4 ? '-v4' : '';
        $folderName = $folderTitle . $type;
        // make file path structure
        $filePath = $folderName . '/' . date('Y') . '/' . date('m') . '/';
        //Set public folder path
        //renaming the file
        $name = $column . '_' . time() . '_' . rand(5000, 100000) . ".png" ;
        $folderPath = public_path('/') . $filePath;

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        // Save File On Local
        file_put_contents($filePath.$name, $file);
        if(env('AWS_ENV'))
        {
            $allFiles = File::allFiles($filePath);
            foreach($allFiles as $singleFile)
            {
                if($singleFile->getFilename() != $name)
                {
                    continue;
                }
                // $amazon_file = $singleFile;
                Storage::disk('s3')->putFileAs($filePath, $singleFile, $name);
            }
            File::delete($singleFile);
        }

        return $filePath . $name;

    }

    protected function deleteFile($file)
    {

        if (env('AWS_ENV')) {
            if (Storage::disk('s3')->delete($file)) {
                return true;
            } else return false;
        } else {
            if(File::exists($file))
            {
                File::delete($file);
                return true;
            }else
            {
                return false;
            }

        }
    }

    protected function createDefaultScedule($type, $model)
    {
        $schedule = Schedule::where('user_id', 1)->where('default', true)->whereWaterType($model->water_type ? $model->water_type : WATER_MARINE)->first();
        if(!$schedule){
            $schedule = Schedule::where('user_id', 1)->where('default', true)->first();
        }
        $newSchedule = $schedule->replicate();
        $newSchedule->name = $model->name . " Default";
        $newSchedule->user_id = Auth::id();
        if ($type == DEFAULT_SCHEDULE_DEVICE) {
            $newSchedule->device_id = $model->id;
        } else {
            $newSchedule->group_id = $model->id;
        }
        if ($schedule->type == SCHEDULE_EASY) {
            $newSchedule->easy_slots = json_decode(json_encode($schedule->easy_slots), TRUE);
        }
        $newSchedule->enabled = true;
        $newSchedule->default = false;
        $newSchedule->save();
        return $newSchedule;
    }
}
