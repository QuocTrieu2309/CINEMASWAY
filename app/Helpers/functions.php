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
//Work with Policy
function CheckPermissionWithPolicy($model, $module)
{
    $permissions = $model->permission()->pluck('name')->toArray();
    if($model->role->name == 'Admin'){
        return true; 
    }
    if (!in_array($module, $permissions) || ($model->role->name !== 'Admin')) {
        return false;
    }
    return true;
}
// Get Public_id image Cloudinary
function getImagePublicId($imageUrl)
{
    $pathParts = explode('/', parse_url($imageUrl, PHP_URL_PATH));
    $filename = end($pathParts);
    $publicId = pathinfo($filename, PATHINFO_FILENAME);

    if (count($pathParts) > 2) {
        $folderPath = implode('/', array_slice($pathParts, -3, 2));
        $publicId = $folderPath . '/' . $publicId;
    }
    return $publicId;
}
// Check kí tự trong 1 hàng
function countUniqueCharacters($string)
{
    $stringWithoutX = str_replace('X', '', $string);
    $uniqueChars = [];
    for ($i = 0; $i < strlen($stringWithoutX); $i++) {
        $uniqueChars[$stringWithoutX[$i]] = true;
    }
    return count($uniqueChars);
}
