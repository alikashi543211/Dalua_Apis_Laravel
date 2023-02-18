<?php

function errorResponse($message, $code)
{
    return response()->json([
        'success' => false,
        'message' => [$message]
    ], $code);
}

function successResponse($message)
{
    return response()->json([
        'success' => true,
        'message' => $message
    ], SUCCESS_200);
}

function successDataResponse($message, $data)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], SUCCESS_200);
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
