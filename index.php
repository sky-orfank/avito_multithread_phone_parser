<?php
header('Content-Type: text/html; charset= utf-8');
include('functions.php');
set_time_limit(36000);

require("RollingCurl.php");

$connect = mysql_connect('localhost', 'root', 'pwd') or die("Невозможно установить соединение".mysql_error( ));

$data = array();
// a little example that fetches a bunch of sites in parallel and echos the page title and response info for each request
function request_callback($response, $info, $request) {
    
	$phone = getAvitoPhone($request->url);
	$phone = preg_replace("/^8/", "+7", $phone);


	preg_match('/<strong itemprop="name">(.+)<\/strong>/isU', $response, $m2);		
	$contact = trim($m2[1]);

	preg_match('/<div id="desc_text" itemprop="description">(.+)<\/div>/isU', $response, $m3);		
	$desc = $m3[1];

	preg_match('/<span itemprop="name">(.+)<\/span>/isU', $response, $m4);		
	$city = $m4[1];

	preg_match('/<span id="item_id">(.+)<\/span>/isU', $response, $m5);		
	$id = $m5[1];

	preg_match('/<h1 itemprop="name" class="h1">(.+)<\/h1>/isU', $response, $m6);		
	$title = $m6[1];

	$data[$request->url]['phone'] = $phone;
	$data[$request->url]['contact'] = $contact;
	$data[$request->url]['desc'] = $desc;
	$data[$request->url]['city'] = $city;
	$data[$request->url]['id'] = $id;
	$data[$request->url]['title'] = $title;


	$q = mysql_query("SELECT * FROM sended_ids WHERE avito_id=".$id);
	$num_rows = mysql_num_rows($q);
	if($num_rows==0) {
		$q = mysql_query("insert into sended_ids (avito_id) values (".$id.")");
	}

	print_r($data); //die();
	echo "<hr>";
}

echo "<hr>";



$url_main = "https://www.avito.ru/moskva/zaprosy_na_uslugi/it_internet_telekom/sozdaniye_i_prodvizheniye_saytov?p=1";
   
$data = array();

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,$url_main);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
//curl_setopt($ch, CURLOPT_PROXY, '213.219.244.175:7951');   
//curl_setopt($ch, CURLOPT_PROXYUSERPWD,'rp1050829:KC93kJMX2Z'); 

$server_output = curl_exec ($ch);

curl_close ($ch);

preg_match('/([0-9]+)">Последняя/isU', $server_output, $mpage);
$pages = $mpage[1];


for ($i = 1; $i <= $pages; $i++) {

	$url_main = "https://www.avito.ru/moskva/zaprosy_na_uslugi/it_internet_telekom/sozdaniye_i_prodvizheniye_saytov?p=".$i;
	   
        $data = array();

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url_main);
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

	$server_output = curl_exec ($ch);

	curl_close ($ch);


	preg_match_all('/<h3 class="title">(.+)<\/h3>/isU', $server_output, $m);
	 
        $urls = array();
	foreach ($m[1] as $item) {
	    preg_match_all('/<a href="(.+)"/isU', $item, $m1);		
	    $urls[] = "https://www.avito.ru".$m1[1][0];
	}

	//print_r($urls);

	$rc = new RollingCurl("request_callback");
	$rc->window_size = 50;
	foreach ($urls as $url) {
		$request = new RollingCurlRequest($url);
		$rc->add($request);
	}
	$rc->execute();

}



