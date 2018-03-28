<?php
require_once './funcs.php';

//1.captchaチェック
//2.IPアドレスチェック(time)
//3.アドレスチェック(time)
//4.microzeny apiへ送金要求し、結果をjsonでクライアントへ返す

$google_token = (string)$_POST['g_token']??'';

$account_id = (string)$_POST['account_id']??'';

$ip_address = $_SERVER["REMOTE_ADDR"];

$recurl = 'https://www.google.com/recaptcha/api/siteverify';
$redata = [
    'secret' => $recaptcha_sercret,
    'response' => $google_token,
    'remoteip' => $ip_address,
];
$recurl .= '?' . http_build_query($redata);
$reheader = ['Content-Type: application/x-www-form-urlencoded'];
$reoptions = ['http' =>[
 'method' => 'GET',
 'header'  => implode("\r\n", $reheader),
 'ignore_errors' => true
 ]
];
$api_res = file_get_contents($recurl, false, stream_context_create($reoptions));
$api_res = json_decode($api_res, TRUE);
if($api_res['success'] !== true){
	$res_data=[
	"result"=>false,
	"message"=>'Captchaに失敗しました。ページを再読み込みしてください',//単位はZNY
	"r_code"=>1
	];
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($res_data);
    exit;
}

//一時的にダミー返す UIの動作確認用
if($debug_mode){
	//チェックはキャプチャのみ　APIにはアクセスしない
$res_data=[
"result"=>true,
"amount"=>0.0039,//単位はZNY
"r_code"=>0
];
header("Content-Type: application/json; charset=utf-8");
echo json_encode($res_data);
exit;
}

$db = new Database($reward_interval_sec);
$db->connect_mysql($db_host,$db_user,$db_password,$db_name);
$prev_time=$db->get_from_ip($ip_address);
$prev_timei=$db->get_from_id($account_id);
if($prev_time==='no'||$prev_timei==='no'){
	$res_data=[
	"result"=>false,
	"message"=>'エラーが発生しました',
	"time_sec"=>$prev_time,
	"r_code"=>2
	];
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($res_data);
    exit;
}
if($prev_time!=='ok'){
	$res_data=[
	"result"=>false,
	"message"=>'前回受け取ってから時間が経っていません。次に受け取れるのは'.zf_funcs::sec2time($prev_time).'後です。',
	"time_sec"=>$prev_time,
	"r_code"=>2
	];
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($res_data);
    exit;
}
if($prev_timei!=='ok'){
	$res_data=[
	"result"=>false,
	"message"=>'前回受け取ってから時間が経っていません。次に受け取れるのは'.zf_funcs::sec2time($prev_timei).'後です。',
	"time_sec"=>$prev_time,
	"r_code"=>2
	];
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($res_data);
    exit;
}
if($prev_time==='ok'&&$prev_timei==='ok'){
	/*受け取り処理書く*/
	$reward_zny = $zeny_static_reward;
	if(false===$zeny_static_reward){
		$reward_zny = get_faucet_zeny_reward($account_id);
	}
	$re = zf_funcs::send_to($api_key, $account_id, $reward_zny, $ip_address);
	if($re!==false){
		$db->set_from_id($account_id);
		$db->set_from_ip($ip_address);
		$res_data=[
		"result"=>true,
		"amount"=>$reward_zny,//単位はZNY
		"r_code"=>0
		];
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($res_data);
		exit;
	}else{
		$res_data=[
		"result"=>false,
		"message"=>'エラーが発生しました。アカウントIDが存在しないかmicrozenyAPIとの通信に失敗しました',//単位はZNY
		"r_code"=>1
		];
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($res_data);
		exit;
	}
}
