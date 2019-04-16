<?php

//Sample URL : http://pranlin.jdsoftware.com/jdbox/services/getCategoryDetails.php?catid=1000461150&data_city=Mumbai
require_once('../config.php');
require_once('includes/category_details_class.php');


if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }
} else {
    header('Content-Type: application/json');
    $params = json_decode(file_get_contents('php://input'), true);
}

$category_details_obj = new Category_details_class($params);
$category_details_arr = $category_details_obj->getCategoryDetails();
$category_details_str = json_encode($category_details_arr);

print($category_details_str);
