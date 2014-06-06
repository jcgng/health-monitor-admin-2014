<?php
header("content-type: image/png");
$photo = $_REQUEST["photo"];
echo file_get_contents("uploaded/".$photo);