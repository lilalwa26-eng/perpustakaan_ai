<?php
// Simple upload helper for member photos
function upload_member_photo($file, $member_id){
    if(!isset($file['tmp_name']) || empty($file['tmp_name'])) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg','jpeg','png'];
    if(!in_array(strtolower($ext), $allowed)) return false;
    $dir = __DIR__ . '/../../storage/uploads/members';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    $fname = 'member_' . $member_id . '.' . $ext;
    $dest = $dir . '/' . $fname;
    if(move_uploaded_file($file['tmp_name'], $dest)){
        return 'storage/uploads/members/' . $fname;
    }
    return false;
}
