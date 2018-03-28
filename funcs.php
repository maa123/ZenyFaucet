<?php
require_once './configs.php';

/**
* 関数まとめたい
*/
class zf_funcs{
	
	function __construct(){
	}
	public static function sec2time($sec){
		$h = floor( $sec / 3600 );
		$m = floor( ( $sec / 60 ) % 60 );
		$s = $sec % 60;
		return $h."時間".$m."分".$s."秒";
	}
	public static function send_to($api_key, $account_id, $amount = 0, $ip_address = ""){
		$api='https://faucet.microzeny.com/api/v1/send';
		$htquery=[
		'api_key'=>$api_key,
		'currency'=>'ZNY',
		'to'=>$account_id,
		'amount'=>$amount,
		'ip_address'=>$ip_address
		];
		$htdata=http_build_query($htquery, "", "&");
		$context = stream_context_create(['http' => [
			'ignore_errors' => true,
			"method"  => "POST",
			"header"  => implode("\r\n", ["Content-Type: application/x-www-form-urlencoded","Content-Length: ".strlen($htdata)]),
			"content" => $htdata
			]]);
		$r=@file_get_contents($api,false,$context);
		//var_dump($r);
		if($r!==''&&$r!==false){
			$arr=json_decode($r,true);
			if(($arr['status']??0) === 200){
				if(($arr['message']??'')==='OK'){
					return true;
				}
				//api error
				return false;
			}else{
				//connection error
				return false;
			}
		}
		return false;
	}
}
/**
* 
*/
class Database{
	function __construct($reward_interval_sec = null){
		$this->reward_interval_sec=$reward_interval_sec;
	}
	function connect_mysql($host, $user, $pass, $dbname){
		try {
			$this->db = new PDO("mysql:host=".$host."; dbname=".$dbname."; charset=utf8", $user, $pass);
		}catch(PDOException $e) {
			echo $e->getMessage();
			die();
		}
	}

	function connect_sqlite($dbname){
		try {
			$this->db = new PDO("sqlite:".$dbname);
		}catch(PDOException $e) {
			echo $e->getMessage();
			die();
		}
	}

	function install_init(){
		try{
			$sql = 'CREATE TABLE IF NOT EXISTS accountid_list (
				id int(32) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
				account_id varchar(75) NOT NULL UNIQUE,
				last int(32) NOT NULL
				) engine=innodb default charset=utf8';
			$res = $this->db->query($sql);
			$sql = 'CREATE TABLE IF NOT EXISTS ip_list (
				id int(32) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
				ip_address varchar(50) NOT NULL UNIQUE,
				last int(32) NOT NULL
				) engine=innodb default charset=utf8';
			$res = $this->db->query($sql);
		}catch(PDOException $e) {
			echo $e->getMessage();
			die();
		}
	}
	function get_from_id($account_id = ""){
		$res = $this->db->prepare("SELECT * FROM accountid_list WHERE account_id = ?");
		$res -> execute([$account_id]);
		$row = $res->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			return 'ok';
		}else{
			if(time()-$row['last']>$this->reward_interval_sec){
				return 'ok';
			}else{
				return $this->reward_interval_sec-time()+$row['last'];
			}

		}
		return 'no';
	}
	function get_from_ip($ip_address = ""){
		$res = $this->db->prepare("SELECT * FROM ip_list WHERE ip_address = ?");
		//$res -> bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
		$res -> execute([$ip_address]);
		$row = $res->fetch(PDO::FETCH_ASSOC);
		//var_dump($row);
		if(!$row){
			return 'ok';
		}else{
			if(time()-$row['last']>$this->reward_interval_sec){
				return 'ok';
			}else{
				return $this->reward_interval_sec-time()+$row['last'];
			}

		}
		return 'no';
	}
	function set_from_id($account_id = ""){
		$res = $this->db->prepare("SELECT * FROM accountid_list WHERE account_id = ?");
		$res -> execute([$account_id]);
		$row = $res->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			$res = $this->db->prepare("INSERT INTO accountid_list (account_id, last) VALUES (?, ?)");
			$res -> execute([$account_id,time()]);
			//insert
			//return 'ok';
		}else{
			//update
			$res = $this->db->prepare("UPDATE accountid_list SET last = ? WHERE account_id = ?");
			$res -> execute([time(),$account_id]);
		}
		return true;
	}
	function set_from_ip($ip_address = ""){
		$res = $this->db->prepare("SELECT * FROM ip_list WHERE ip_address = ?");
		$res -> execute([$ip_address]);
		$row = $res->fetch(PDO::FETCH_ASSOC);
		if(!$row){
			$res = $this->db->prepare("INSERT INTO ip_list (ip_address, last) VALUES (?, ?)");
			$res -> execute([$ip_address,time()]);
			//insert
			//return 'ok';
		}else{
			//update
			$res = $this->db->prepare("UPDATE ip_list SET last = ? WHERE ip_address = ?");
			$res -> execute([time(),$ip_address]);
		}
		return true;
	}
}
?>