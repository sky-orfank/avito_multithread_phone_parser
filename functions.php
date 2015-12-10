<?php
	function execCurl( $url, $referrer = false, $is_xml = false )
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:16.0) Gecko/20100101 Firefox/16.0');

		if($referrer)
			curl_setopt($ch, CURLOPT_REFERER, $referrer);

		if($is_xml)
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-requested-with: XMLHttpRequest']);

		curl_setopt($ch, CURLOPT_URL, $url);

		$html = trim(curl_exec($ch));
		curl_close($ch);

		return $html;
	}

	function getAvitoPhone( $url )
	{
		$url = str_replace('https://www.', 'https://m.', $url);
		if($html = execCurl($url, 'https://www.avito.ru/'))
		{
			if(preg_match('#href="(.*?)" title="Телефон продавца"#', $html, $match))
			{
				$phoneUrl = "https://m.avito.ru{$match[1]}?async";
				if($json = json_decode(execCurl($phoneUrl, $url, true), true))
					return $json['phone'];
			}
		}
		return false;
	}
?>