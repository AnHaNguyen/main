<?php
require("data.php");
$data = array_merge($ULR, $PR, $UE);
echo json_encode($data);
?>
