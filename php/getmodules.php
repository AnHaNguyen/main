<?php
require('data.php');
$data = array_merge($ULR, $UE, $PR);
echo json_encode($data);
?>
