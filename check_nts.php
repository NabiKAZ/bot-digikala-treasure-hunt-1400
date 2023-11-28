<?php
/*************************************************************************
 _   _       _     _   _  __     _      _____  
| \ | | __ _| |__ (_) | |/ /    / \    |__  /  
|  \| |/ _` | '_ \| | | ' /    / _ \     / /   
| |\  | (_| | |_) | | | . \ _ / ___ \ _ / /_ _ 
|_| \_|\__,_|_.__/|_| |_|\_(_)_/   \_(_)____(_)
**************************************************************************
* @Author       Nabi KaramAliZadeh
* @Website      www.Nabi.ir
* @Email        nabikaz@gmail.com
* @Package      Bot Digikala Treasure Hunt (Non-Thread Safe version)
* @Version      1.0.0
* @Project      https://github.com/NabiKAZ/bot-digikala-treasure-hunt-1400
* @Copyright 2021 Nabi K.A.Z. , All rights reserved.
* @Released under the terms of the GNU General Public License v3.0
*************************************************************************/

if (PHP_SAPI === 'cli') parse_str(implode('&', array_slice($argv, 1)), $_GET);
$sync = (isset($_GET['sync']) && $_GET['sync'] != '0') ? true : false;//update new images in db, default: false
$force = (isset($_GET['force']) && $_GET['force'] != '0') ? true : false;//force check md5 images, default: false

date_default_timezone_set('Asia/Tehran');
if (!is_dir('images/')) mkdir('images/');

$date = date('Y-m-d_H-i-s');
$result_filename = 'results_' . $date . '.htm';
file_put_contents($result_filename, '<style>div {text-align: center; display: inline-block; border: 2px solid gray; margin: 2px; padding: 5px;}</style><h2>' . $date . '</h2>'."\n");

$db = new PDO('sqlite:images.sqlite');

$page = 1;

do {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.digikala.com/ajax/treasure-hunt/products/?pageno=' . $page);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
	$headers = array();
	$headers[] = 'Authority: www.digikala.com';
	$headers[] = 'Sec-Ch-Ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"96\", \"Google Chrome\";v=\"96\"';
	$headers[] = 'Accept: text/html, */*; q=0.01';
	$headers[] = 'X-Requested-With: XMLHttpRequest';
	$headers[] = 'Sec-Ch-Ua-Mobile: ?0';
	$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36';
	$headers[] = 'Sec-Ch-Ua-Platform: \"Windows\"';
	$headers[] = 'Sec-Fetch-Site: same-origin';
	$headers[] = 'Sec-Fetch-Mode: cors';
	$headers[] = 'Sec-Fetch-Dest: empty';
	$headers[] = 'Referer: https://www.digikala.com/treasure-hunt/products/';
	$headers[] = 'Accept-Language: en-US,en;q=0.9,fa;q=0.8,de;q=0.7';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error: ' . curl_error($ch);
	}
	curl_close($ch);

	$result = json_decode($result, true);
	$products = $result['data']['click_impression'];
	
	$pages = $result['data']['trackerData']['pages'];
	
	echo '>>>>>>>>> Page #1' . ' | Total: ' . count($products) . "\n";

	foreach ($products as $product) {
		$pid = $product['id'];
		$name = $product['name'];
		$product_url = $product['product_url'];
		echo '> PID: dkp-' . $pid . ' | ' . $name . "\n";
		
		$product_url = str_replace('https://digikala.com', 'https://www.digikala.com', $product_url);
		
		$pos = strrpos($product_url, '/');
		$product_url_encoded = substr($product_url, 0, $pos + 1) . urlencode(substr($product_url, $pos + 1));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $product_url_encoded);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		$headers = array();
		$headers[] = 'Authority: www.digikala.com';
		$headers[] = 'Cache-Control: max-age=0';
		$headers[] = 'Sec-Ch-Ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"96\", \"Google Chrome\";v=\"96\"';
		$headers[] = 'Sec-Ch-Ua-Mobile: ?0';
		$headers[] = 'Sec-Ch-Ua-Platform: \"Windows\"';
		$headers[] = 'Upgrade-Insecure-Requests: 1';
		$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36';
		$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
		$headers[] = 'Sec-Fetch-Site: none';
		$headers[] = 'Sec-Fetch-Mode: navigate';
		$headers[] = 'Sec-Fetch-User: ?1';
		$headers[] = 'Sec-Fetch-Dest: document';
		$headers[] = 'Accept-Language: en-US,en;q=0.9,fa;q=0.8,de;q=0.7';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error: ' . curl_error($ch);
		}
		curl_close($ch);
		
		$re = '/\"image\"\: \[(.*?)\],/s';
		preg_match($re, $result, $matches);
		$images = json_decode('[' . $matches[1] . ']');
		foreach ($images as $image) {
			$filename = basename(parse_url($image, PHP_URL_PATH));
			
			if (substr($filename, -4, 4) == '.mp4') continue;
			
			$image = str_replace('image/resize,h_1600/quality,q_80/watermark,image_ZGstdy8xLnBuZw==,t_90,g_nw,x_15,y_15', 'image/resize,h_200/quality,q_10', $image);
			
			if ($force) {
				file_put_contents('images/' . $filename, file_get_contents($image));
				$hash = md5_file('images/' . $filename);
			}
			
			$result = $db->prepare('SELECT * FROM images WHERE pid = ? AND filename = ? LIMIT 1');
			$result->execute([$pid, $filename]);
			$row = $result->fetch();
			
			if ($row) {
				echo 'Image existed... ';
				
				if ($force) {
					if ($row['hash'] == $hash) {
						echo 'No changed.';
					} else {
						
						if ($sync) {
							$result = $db->prepare('UPDATE images SET hash = ? WHERE id = ?');
							if ($result->execute([$hash, $row['id']])) {
								echo 'Updated.' . '(' . $filename . ')';
							} else {
								echo 'Error.';
							}
						} else {
							echo 'Skip.' . '(' . $filename . ')';
						}
						
						save_result($filename, $pid, false);
						
					}
				} else {
					echo 'Ok.';
				}
				
			} else {
				echo 'Found new image... ';
				
				if (!$force) {
					file_put_contents('images/' . $filename, file_get_contents($image));
					$hash = md5_file('images/' . $filename);
				}
				
				if ($sync) {
					$result = $db->prepare('INSERT INTO images(pid, filename, hash) VALUES (?, ?, ?)');
					if ($result->execute([$pid, $filename, $hash])) {
						echo 'Saved.' . '(' . $filename . ')';
					} else {
						echo 'Error.';
					}
				} else {
					echo 'Skip.' . '(' . $filename . ')';
				}
				
				save_result($filename, $pid, true);
				
			}
			
			echo "\n";
			
		}

	}
	
	$page++;
	
} while ($page <= $pages);

function save_result($filename, $pid, $new) {
	global $result_filename;
	file_put_contents($result_filename, '<div ' . ($new ? 'style="border-color:#01f;"' : '') . '><a href="https://dkstatics-public.digikala.com/digikala-products/' . $filename . '?x-oss-process=image/resize,h_800/quality,q_80" target="_blank"><img src="https://dkstatics-public.digikala.com/digikala-products/' . $filename . '?x-oss-process=image/resize,h_100/quality,q_40"></a><br><a href="https://www.digikala.com/product/dkp-' . $pid . '" target="_blank">dkp-' . $pid . '</a></div>'."\n", FILE_APPEND);
}
