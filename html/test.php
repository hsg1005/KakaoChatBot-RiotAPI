<?php
//test file

//한글 깨짐 방지하는 UTF-8인코딩
header("Content-Type: text/html; charset=UTF-8");

$input_message = "input_message";
//자신의 api_key
$api_key = "api_key";
//대문자를 소문자로 변환
//양쪽 공백 제거
$username = strtolower(trim($input_message));
//summonerNames //usernames에서 공백(" ")을 ""로 대체
$username = preg_replace("/\s+/", "", $username); 

//SUMMONER-V3 {summonerName}
$summoners_by_name = json_decode(httpGet("https://kr.api.riotgames.com/lol/summoner/v3/summoners/by-name/".$username."?api_key=".$api_key), true);

$summoner_id = $summoners_by_name[id];
$summoner_level = $summoners_by_name['summonerLevel'];

//LEAGUE-V3 {summonerId}
$league_position_by_summoner = json_decode(httpGet("https://kr.api.riotgames.com/lol/league/v3/positions/by-summoner/".$summoner_id."?api_key=".$api_key), true);

$league_queue_type = $league_position_by_summoner[0]['queueType'];
$league_queue_type1 = $league_position_by_summoner[1][queueType];
$league_wins = $league_position_by_summoner[0]['wins'];
$league_wins1 = $league_position_by_summoner[1]['wins'];

$league_losses = $league_position_by_summoner[0]['losses'];
$league_losses1 = $league_position_by_summoner[1]['losses'];

$league_rank = $league_position_by_summoner[0]['rank'];
$league_rank1 = $league_position_by_summoner[1]['rank'];

$league_tier = $league_position_by_summoner[0]['tier'];
$league_tier1 = $league_position_by_summoner[1]['tier'];

$league_league_points = $league_position_by_summoner[0]['leaguePoints'];
$league_size_array = count($league_position_by_summoner);

echo $league_size_array . "<br>";



echo "summoners_by_name ". $summoners_by_name . "<br>";
echo "summoner_id ". $summoner_id . "<br>";
echo "summoner_level ". $summoner_level . "<br>";
echo "league_position_by_summoner ". $league_position_by_summoner . "<br>";

echo "league_queue_type ". $league_queue_type . "<br>";
echo "league_queue_type1 ". $league_queue_type1 . "<br>";
echo "league_wins ". $league_wins . "<br>";
echo "league_wins1 ". $league_wins1 . "<br>";

echo "league_losses ". $league_losses . "<br>";
echo "league_losses1 ". $league_losses1 . "<br>";
echo "league_rank ". $league_rank . "<br>";
echo "league_rank1 ". $league_rank1 . "<br>";
echo "league_tier ". $league_tier . "<br>";
echo "league_tier1 ". $league_tier1 . "<br>";
echo "league_tier ". $league_tier . "<br>";
echo "league_league_points ". $league_league_points . "<br>";




echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";


$userid = $get_id[$username]['id'];
$status = json_decode(httpGet("https://kr.api.pvp.net/api/lol/kr/v2.5/league/by-summoner/".$userid."/entry?api_key=".$api_key), true);
$current_game = json_decode(httpGet("https://kr.api.pvp.net/observer-mode/rest/consumer/getSpectatorGameInfo/KR/".$userid."?api_key=".$api_key), true);
$matchlist = json_decode(httpGet("https://kr.api.pvp.net/api/lol/kr/v2.2/matchlist/by-summoner/".$userid."?api_key=".$api_key), true);
$lol_static_data_v3 = json_decode(httpGet("https://kr.api.pvp.net//lol/static-data/v3/champions?api_key=".$api_key), true);
	
$nick = $get_id[$username]['name'];
$level = $get_id[$username]['summonerLevel'];
//$profileIcon = $get_id[$username]['profileIconId'];
$tier = $status[$userid][0]['tier'];
$division = $status[$userid][0]['entries'][0]['division'];
$LeaguePoint = $status[$userid][0]['entries'][0]['leaguePoints'];
$win = $status[$userid][0]['entries'][0]['wins'];
$lose = $status[$userid][0]['entries'][0]['losses'];

echo "input_message ". $input_message . "<br>";
echo "input ". $input . "<br>";
//echo "conn ". $conn . "<br>";
echo "usernames ". $usernames . "<br>";
echo "username ". $username . "<br>";
echo "get_id ". $get_id . "<br>";
echo "userid ". $userid . "<br>";
echo "current_season ". $current_season . "<br>";
echo "level ". $level . "<br>";




function httpGet($url)
{
    $ch = curl_init();  
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false);
	//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $output = curl_exec($ch);
    curl_close($ch);
	
    return $output;
}

function send_text($text){
	
	echo json_encode(array('message' => array('text' => $text)));
	
}
function send_text_buttons($text, $buttons){
	
	echo json_encode(array('message' => array('text' => $text), 'keyboard' => array('type' => 'buttons', 'buttons' => $buttons)));
}
function send_text_label_buttons($text, $label, $link_url, $buttons){
	
	echo json_encode(array('message' => array('text' => $text, 'message_button' => array('label' => $label, 'url' => $link_url)), 'keyboard' => array('type' => 'buttons', 'buttons' => $buttons)));
	
	
}
function send_text_label($text, $label, $link_url){
	
	echo json_encode(array('message' => array('text' => $text, 'message_button' => array('label' => $label, 'url' => $link_url))));
	
	
}
function send_text_image_buttons($text, $image, $buttons){
	
	echo json_encode(array('message' => array('text' => $text, 'photo' => array('url' => $image, 'width' => 640, 'height' => 480)), 'keyboard' => array('type' => 'buttons', 'buttons' => $buttons)));			
				
	
}			
function send_text_image_label($text, $image, $label, $link_url){
	
	echo json_encode(array('message' => array('text' => $text, 'photo' => array('url' => $image, 'width' => 640, 'height' => 480), 'message_button' => array('label' => $label, 'url' => $link_url))));
	
}
					
function send_text_image_label_buttons($text, $image, $label, $link_url, $buttons){
	
	echo json_encode(array('message' => array('text' => $text, 'message_button' => array('label' => $label, 'url' => $link_url), 'photo' => array('url' => $image, 'width' => 640, 'height' => 480)), 'keyboard' => array('type' => 'buttons', 'buttons' => $buttons)));			
				
	
}

?>