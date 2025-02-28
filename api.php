<?php

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://paddy-power-gambling-games.p.rapidapi.com/game_list?token=BYnb4QFHM4",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"x-rapidapi-host: paddy-power-gambling-games.p.rapidapi.com",
		"x-rapidapi-key: 744f86c602msh2e1354c964a4d9ep15fa8cjsnd7f6fadca222"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	echo $response;
}