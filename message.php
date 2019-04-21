<?php


$servername = "servername";
$username = "username";
$password = "password";
$dbname = "dbname";
// DB 접속하기
$conn = mysqli_connect($servername, $username, $password, $dbname);
$input = json_decode(file_get_contents('php://input'), true);
$input_message = $input['content'];
$user_key = $input['user_key'];
// message_log 테이블에 사용자 메시지와 사용자 키를 저장함
$sql = "INSERT INTO message_log (message, user_key) VALUES ('$input_message', '$user_key')";
// 한글 처리 및 쿼리문 실행
mysqli_query($conn, 'set names utf8');
$conn->query($sql);
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//한글 깨짐 방지하는 UTF-8인코딩
//header("Content-Type: text/html; charset=UTF-8");
//자신의 api_key
$api_key = "api_key";
//대문자를 소문자로 변환
//양쪽 공백 제거
$username_tmp = strtolower(trim($input_message));
//summonerNames //usernames에서 공백(" ")을 ""로 대체
$username = preg_replace("/\s+/", "", $username_tmp);

//SUMMONER-V3 {summonerName}
$summoners_by_name =
json_decode(httpGet("https://kr.api.riotgames.com/lol/summoner/v3/summoners/by-name/".$username."?api_key=".$api_key), true);

$summoner_name = $summoners_by_name['name'];
$summoner_id = $summoners_by_name['id'];
$summoner_account_id = $summoners_by_name['accountId'];
$summoner_level = $summoners_by_name['summonerLevel'];

//LEAGUE-V3 {summonerId}
$league_position_by_summoner =
json_decode(httpGet("https://kr.api.riotgames.com/lol/league/v3/positions/by-summoner/".$summoner_id."?api_key=".$api_key), true);

//SPECTATOR-V3 {summonerId}
$active_games_by_summoner =
json_decode(httpGet("https://kr.api.riotgames.com/lol/spectator/v3/active-games/by-summoner/".$summoner_id."?api_key=".$api_key), true);

// MATCH-V3 {accountId}
$matchlists_by_account =
json_decode(httpGet("https://kr.api.riotgames.com/lol/match/v3/matchlists/by-account/".$summoner_account_id."?api_key=".$api_key), true);

//가장 최근 게임 날짜 정보
$timestamp = $matchlists_by_account['matches'][0]['timestamp'];
$date = date("Y-m-d H:i:s", $timestamp/1000);


// 여기서부터 출력 시작한다.
//인게임 정보
$output_message = " ";
if($active_games_by_summoner['status']['status_code'] == 404){
	$output_message = $output_message. "(하하)인게임 정보가 없습니다.\n\n";
}
else{
	$output_message = $output_message. "(하하)인게임 정보\n\n";
	$participants = $active_games_by_summoner['participants'];
	for($i = 0; $i < 10; $i++){
		if($i == 5)
			$output_message = $output_message. "VS\n";
		$champion_id = $participants[$i]['championId'];
		//LOL-STATIC-DATA-V3 {id}
		$champions =
		json_decode(httpGet("https://kr.api.riotgames.com/lol/static-data/v3/champions/".$champion_id."?locale=ko_KR&api_key=".$api_key), true);
		$output_message = $output_message. " " . $champions['name'] . " " . $participants[$i]['summonerName'] . "\n";
	}
	$output_message = $output_message. "\n";
}
if($summoners_by_name['status']['status_code'] == 404 ){
	send_text("존재하지 않는 아이디입니다(헉)");
}
$output_message = $output_message. "소환사명 : " . $summoner_name. "\n";
$output_message = $output_message. "레벨 : " . $summoner_level. "\n";
$output_message = $output_message. "최근 게임 : " . $date . "\n\n";

if($league_position_by_summoner['status']['status_code'] == 404 ){
	$output_message = $output_message. "매치기록이 없습니다(헉)";
	send_text($output_message);
}
//현재 시즌 리그정보
for($i = 0; $i < count($league_position_by_summoner); $i++){
	$league_queue_type =  $league_position_by_summoner[$i]['queueType'];
	
	if($league_queue_type == "RANKED_FLEX_SR"){
		$output_message = $output_message. "<자유랭>\n";
	}
	elseif($league_queue_type == "RANKED_SOLO_5x5"){
		$output_message = $output_message. "<솔랭>\n";
	}
	$league_wins = $league_position_by_summoner[$i]['wins'];
	$league_losses = $league_position_by_summoner[$i]['losses'];
	$league_rank = $league_position_by_summoner[$i]['rank'];
	$league_tier = $league_position_by_summoner[$i]['tier'];
	$league_league_points = $league_position_by_summoner[$i]['leaguePoints'];
	
	$output_message = $output_message. "티어 : " . $league_tier . " " . $league_rank. "\n";
	$output_message = $output_message. "LP : " . $league_league_points. "\n";
	$output_message = $output_message. "전적 : " . $league_wins . "승 " . $league_losses . "패 " .
	"(승률 : " . round($league_wins/($league_wins+$league_losses)*100,2) . "%)\n\n"; //승률 소수점 2자리 반올림
}
send_text($output_message);

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