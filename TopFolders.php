<?php

define('USER_ACCESS_FILE_PATH', './user_access.csv');
define('FOLDERS_FILE_PATH', './folders.csv');
define('TOP_FOLDERS_RESULT_FILE_PATH', './folders_top_user_permission.txt');

function get_user_folders($user_id) {
    //get user access file content and filter folders by user
    $user_folders = array_filter(array_map('str_getcsv', file(USER_ACCESS_FILE_PATH)), function($v, $k) use($user_id){
        return $v[1] == $user_id;
    }, ARRAY_FILTER_USE_BOTH);
    // convert indexed array to associative array for efficiency and remove duplicate rows
    return array_column($user_folders, null, 0);
}

function get_group_research($id_group_research) {
    //get folders file content and filter folders by group research
    $group_folders = array_filter(array_map('str_getcsv', file(FOLDERS_FILE_PATH)), function($v, $k) use($id_group_research){
        return $v[2] == $id_group_research;
    }, ARRAY_FILTER_USE_BOTH);
    // convert indexed array to associative array for efficiency and remove duplicate rows
    return array_column($group_folders, null, 0);
}

function set_top_folders_per_user_and_rg($user_id, $id_group_research) {
    $user_folders_with_access = get_user_folders($user_id);
    $group_folders = get_group_research($id_group_research);
    foreach ($group_folders as $key => $value) {
        //check if user_id have access to this folder
        if (!array_key_exists($key, $user_folders_with_access)) {
            $group_folders[$key]['top-folder'] = false;
            $parent = $group_folders[$key][1];
            //if user haven't access set all parent of this folder top-folder = false
            while(is_numeric($parent)) {
                $group_folders[$parent]['top-folder'] = false;
                $parent = $group_folders[$parent][1];
            }
        }
    }
    //filter top folders
    $top_folders =  array_filter($group_folders, function($v, $k) use($group_folders) {
        return !array_key_exists('top-folder' , $v) && (!is_numeric($v[1]) || array_key_exists('top-folder' , $group_folders[$v[1]]));
    }, ARRAY_FILTER_USE_BOTH);

    write_top_folders_to_file($top_folders, $user_id, $id_group_research);
}

function write_top_folders_to_file($top_folders, $user_id, $group_research) {
    $file = fopen(TOP_FOLDERS_RESULT_FILE_PATH, "w");
    fwrite($file, "Top Folders For User Number " . $user_id . " And Group Research Number " . $group_research . "\n");
    fwrite($file, implode(",\n",array_keys ($top_folders)));
    fclose($file);
}
//run with args from command line
if (count($argv) > 2) {
    set_top_folders_per_user_and_rg($argv[1], $argv[2]);
}
else {
    set_top_folders_per_user_and_rg(2, 27104);
}




