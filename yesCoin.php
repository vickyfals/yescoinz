<?php
/* @RiyanCoday - 23/05/2024 */
/* auto collect */
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
$collectCoinUrl = "https://api.yescoin.gold/game/collectCoin";
$collectSpecialBoxCoinUrl = "https://api.yescoin.gold/game/collectSpecialBoxCoin";
$token = file_get_contents("token.txt");
$headers = [
    "accept: application/json, text/plain, */*",
    "accept-language: en-US,en;q=0.9",
    "content-type: application/json",
    "origin: https://www.yescoin.gold",
    "priority: u=1, i",
    "referer: https://www.yescoin.gold/",
	"token: ".$token,
    "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0"
];

function postC($url, $postData, $headers) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch) . "\n";
    }
	return $response;

    curl_close($ch);
}
function getC($url,$headers){
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HTTPGET, true);
$response = curl_exec($ch);
return $response;
}
function readyC($headers) {
    $urlG = "https://api.yescoin.gold/game/getGameInfo";
    $chkG = getC($urlG, $headers);
    $jsG = json_decode($chkG, true);
    $clc = $jsG['data']['singleCoinValue'];
    $te = $jsG['data']['coinPoolTotalCount'];
    $ae = $jsG['data']['coinPoolLeftCount'];
if(!$jsG['data']['singleCoinValue']){ $c = 0; 
$response = [
        'coday' => $c,
    ];
} else { $c = 8; 
    $response = [
		'readyc' => ceil(($ae/$clc)/8),
        'singleCoinValue' => $clc,
        'coinPoolTotalCount' => $te,
        'coday' => $c,
        'coinPoolLeftCount' => $ae
    ];
}
    return json_encode($response);
}		
while(true){
	
   $normalCoinData = json_decode(readyC($headers),true);
if($normalCoinData['coday'] == 0){
echo "\033[31mPastiin token bener! \033[0m\n";  
// exit();
}
   $collect = postC($collectCoinUrl, $normalCoinData['readyc'], $headers);
   /* box */
		$urlbox = "https://api.yescoin.gold/game/getSpecialBoxInfo";
		$chkBox = getC($urlbox,$headers);
		$jsbox = json_decode($chkBox, true);
		$autoBox = $jsbox['data']['autoBox'];
        $recoveryBox = $jsbox['data']['recoveryBox'];
	if($autoBox['boxStatus'] == true){
					$specialBoxData = [
						"boxType" => 1,
						"coinCount" => $autoBox['specialBoxTotalCount']
					];
	$collectbox = postC($collectSpecialBoxCoinUrl, $specialBoxData, $headers);
	$datax = json_decode($collectbox, true);
	$collectBoxAmount = $datax['data']['collectAmount'];
	}else if($recoveryBox['boxStatus'] == true){
					$specialBoxData = [
						"boxType" => 2,
						"coinCount" => $recoveryBox['specialBoxTotalCount']
					];
	$collectbox = postC($collectSpecialBoxCoinUrl, $specialBoxData, $headers);
	$datax = json_decode($collectbox, true);
	$collectBoxAmount = $datax['data']['collectAmount'];
	}else{
		$collectBoxAmount = 0;
	}
	if($collectBoxAmount > 1){
	$msg = "collect box $collectBoxAmount coin";
	}else{
	$msg = "no box";
	}
	$data = json_decode($collect, true);
        if (isset($data['code']) && $data['code'] === 0) {
            if (isset($data['data']['collectAmount'])) {
		$urlI = "https://api.yescoin.gold/account/getAccountInfo";
		$chkI = getC($urlI,$headers);
		$jsI = json_decode($chkI, true);
		$userLevel = $jsI['data']['userLevel'];
        $currentAmount = $jsI['data']['currentAmount'];
		$coin = number_format($currentAmount,0,',','.');
        //$totalAmount = $jsI['data']['totalAmount'];
        //$rank = $jsI['data']['rank'];
                $collectAmount = $data['data']['collectAmount'];
                echo "\033[32mSuccess collect {$collectAmount} coin, {$msg} [Level:{$userLevel} , Coin: {$coin} , Pool: {$normalCoinData['coinPoolLeftCount']}]\033[0m\n";
            }
        }else if ($data['message'] == "left coin not enough"){
			echo "\033[33mKumpulin energi 1menit\033[0m\n";  
			sleep(60);
		} else {
            echo "\033[31mResponse: " . $data['message'] . "\033[0m\n";  
        }
}
?>
