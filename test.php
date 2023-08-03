<?php 
// $a = json_encode([
// 	'j' => '2', 'k' => '3','l'=> '5'
// ]);

// print_r( json_decode($a) );
// print_r( json_decode($a, true) );

$a = "{\"slide_order\":\"[\\\"6\\\",\\\"7\\\"]\",\"slide_start\":\"1\",\"slide_end\":\"0\",\"slide_limit\":\"1\",\"prev_height\":\"200\",\"prev_width\":\"220\",\"prev_is_sq\":\"0\",\"prev_h_max\":\"250\",\"prev_w_max\":\"250\",\"web_height\":\"500\",\"web_width\":\"540\",\"web_is_sq\":\"0\",\"web_h_max\":\"1080\",\"web_w_max\":\"1920\",\"alignment\":\"0\"}";

print_r(json_decode($a, true));


?>