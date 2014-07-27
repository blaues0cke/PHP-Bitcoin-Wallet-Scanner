<pre>
<meta http-equiv="refresh" content="2; URL=http://localhost/walletSearcher/search.php">
<?php

set_time_limit(3600);

$blacklist = array
(
	'<!doc',
	'<!DOCTYPE',
	'<?xml',
	'<html>',
);

mysql_connect('localhost', 'root', '');
mysql_select_db('walletsearcher');

$sql = mysql_query('SELECT * FROM `domains` WHERE `crawled` = 0 ORDER BY RAND() LIMIT 100');

$ua = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
$timeout = 100;

while ($row = mysql_fetch_assoc($sql))
{
	$ch = curl_init();
	
	$parsedUrl = parse_url($row['url']);

	$folder = (!empty($parsedUrl['path']) ? substr(dirname($parsedUrl['path']), 1).'/' : '');
	
	$domain = $parsedUrl['scheme'].'://'.$parsedUrl['host'].'/'.$folder.'wallet.dat';
	
	
	echo 'Crawling: '.$domain;
	echo "\r";
	
	curl_setopt($ch, CURLOPT_URL, $domain);
	//curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	curl_setopt ($ch, CURLOPT_USERAGENT, $ua); 

	
	$body = curl_exec($ch);
	
	$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($httpStatus == 200)
	{
		$ignore = false;
	
		foreach ($blacklist as $a => $string)
		{
			if (startsWith($body, $string))
			{
				$ignore = true;
				break;
			}
		}
	
		if (!$ignore)
		{
			echo 'Found file, downloading';
			echo "\r";
		
			file_put_contents('wallets/'.$row['domain_id'].'.dat', $body);
		}
	}
	else
	{
		echo 'Nothing found';
		echo "\r";
	}
	
	curl_close($ch);
	
	$domain2 = $row['url'];
	
	echo 'Crawling: '.$domain2;
	echo "\r";
	
	$ch2 = curl_init();
	curl_setopt($ch2, CURLOPT_URL, $domain2);
	//curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch2, CURLOPT_RETURNTRANSFER, 1) ;
	curl_setopt ($ch2, CURLOPT_CONNECTTIMEOUT, $timeout); 
	curl_setopt ($ch2, CURLOPT_USERAGENT, $ua); 

	
	$body = curl_exec($ch2);
	//var_dump(htmlspecialchars($body));
	preg_match_all('!href="((?:https://|http://|//).*?)"!is', $body, $result);
	
	foreach ($result[1] as $key => $value)
	{
		$t = time();
	
		if ($value[0] == '/' && $value[1] == '/')
		{
			$value = 'http:'.$value;
		}
		//var_dump($value);
	
		$parsedUrl = parse_url($value);
		
		if (!empty($parsedUrl['host']))
		{
			echo 'Adding: '.$parsedUrl['host'];
			echo "\r";
		
			mysql_query('INSERT INTO `domains` SET `url` = \''.$value.'\', `creation_time` = '.$t.', `found_last` = '.$t.', `md5hash` = \''.md5($value).'\' ON DUPLICATE KEY UPDATE `found_last` = '.$t);
			
			$error = mysql_error();
			//var_dump($error);
		}
		
		// usleep(2000000); // 2 sek
		//usleep(500000); // 0,5 sek
		
		//usleep(100000); // 0,1 sek
		usleep(50000); // 0,05 sek
		//var_dump($parsedUrl);
	}
	
	curl_close($ch2);
mysql_query('UPDATE `domains` SET `crawled` = 1 WHERE `domain_id` = '.$row['domain_id']);
		echo '---';
		echo "\r";
}

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}