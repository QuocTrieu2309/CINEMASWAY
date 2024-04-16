<?php
// API Response
function ApiResponse($success, $data = null, $statusCode = null, $message = null)
{
    $response = [
        'success' => $success,
        'statusCode' => $statusCode,
        'message' => $message,
    ];

    if ($success) {
        $response['data'] = $data;
    }

    return response()->json($response, $statusCode);
}
// Display Success Data
function messageResponseData()
{
    return 'Hiển thị thông tin thành công';
}


// Not Found Data
function messageResponseNotFound()
{
    return 'Không tìm thấy thông tin, vui lòng thử lại';
}


// Action Failed
function messageResponseActionFailed()
{
    return 'Thao tác không thành công, vui lòng thử lại';
}


// Action Success
function messageResponseActionSuccess()
{
    return 'Thao tác thành công';
}
