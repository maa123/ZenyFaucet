<?php

$api_key = 'Microzeny API KEY';


$recaptcha_sercret = 'Recaptcha Sercret Key';

$reward_interval = 120;//分


#0は指定できません
#$zeny_static_reward = 0.0039;
$zeny_static_reward = false;

#$zeny_static_rewardが0もしくはfalseの時に表示されます
$rewards_view = '0.039~0.00039';

$db_host = 'localhost';

$db_name = 'bitzeny_faucet';

$db_user = 'bitzeny_faucet';

$db_password = 'Password';

session_name("Bitzeny_Faucet");

$reward_interval_sec = $reward_interval*60;//秒に変換

/*
$zeny_static_rewardにfalseが指定されているとこの関数が呼ばれます
*/
function get_faucet_zeny_reward($account_id = ""){
	$rewards = [
	["amount"=>0.039 , "w" => 5],
	["amount"=>0.0039 , "w" => 45],
	["amount"=>0.00039 , "w" => 50]
	];
	$m=0;
	foreach ($rewards as $v) {
		$m=$m+$v['w'];
	}
	$r = mt_rand(0,($m - 1));
	foreach($rewards as $key => $v){
		$m = $m-$v['w'];
		if( $m <= $r ) {return $v['amount'];}
	}

}
$debug_mode= false;
?>