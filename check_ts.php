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
* @Package      Bot Digikala Treasure Hunt (Thread Safe version)
* @Version      1.0.0
* @Project      https://github.com/NabiKAZ/bot-digikala-treasure-hunt-1400
* @Copyright 2021 Nabi K.A.Z. , All rights reserved.
* @Released under the terms of the GNU General Public License v3.0
*************************************************************************/

if (PHP_SAPI === 'cli') parse_str(implode('&', array_slice($argv, 1)), $_GET);
$sync = (isset($_GET['sync']) && $_GET['sync'] != '0') ? true : false;//update new images in db, default: false
$force = (isset($_GET['force']) && $_GET['force'] != '0') ? true : false;//force check md5 images, default: false
$clear = (!isset($_GET['clear']) || $_GET['clear'] == '1') ? true : false;//clear screen, default: true

echo 'Sync=' . var_export($sync, true) . "\n";
echo 'Force=' . var_export($force, true) . "\n";
echo 'Clear=' . var_export($clear, true) . "\n";
echo "===========\n";

date_default_timezone_set('Asia/Tehran');
if (!is_dir('images/')) mkdir('images/');

$date = date('Y-m-d_H-i-s');
$result_filename = 'results_' . $date . '.htm';
file_put_contents($result_filename, '<style>div {text-align: center; display: inline-block; border: 2px solid gray; margin: 2px; padding: 5px;}</style><h2>' . $date . '</h2>'."\n");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.digikala.com/ajax/treasure-hunt/products/?pageno=1');
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
$pages = $result['data']['trackerData']['pages'];


$app = [];
for ($n=1; $n<=$pages; $n++) {

	$app[$n] = new WebRequest($n, $sync, $force, $result_filename);
	$app[$n]->start();
	
	usleep(0.3 * 1000 * 1000);
}

while(true) {
	if ($clear) echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
	$c = 0;
	foreach($app as $anapp) {
		echo implode(' > ', (array)$anapp->log_fixed) . ' > ' . $anapp->log . PHP_EOL;
		if (isset($anapp) && $anapp->isRunning()) {
			$c++;
		}
	}
	if ($c == 0) die;
	usleep(0.2 * 1000 * 1000);
}

die;


//base thread class
class WebRequest extends Thread {
	public $page;
	public $sync;
	public $force;
	public $result_filename;
	public $log_fixed;
	public $log;
	
    public function __construct($page, $sync, $force, $result_filename) {
		$this->page = $page;
		$this->sync = $sync;
		$this->force = $force;
		$this->result_filename = $result_filename;
		$this->log_fixed = [];
		$this->log = '';
    }

    public function run() {
		$db = new PDO('sqlite:images.sqlite');
		$page = $this->page;
		$sync = $this->sync;
		$force = $this->force;
		$result_filename = $this->result_filename;
		
		$this->log_fixed['page'] = 'Page #' . $page;
		
		start:
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.digikala.com/ajax/treasure-hunt/products/?pageno=' . $page);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
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
			$this->log = 'Error: ' . curl_error($ch);
			goto start;
		}
		curl_close($ch);

		$result = json_decode($result, true);
		$products = $result['data']['click_impression'];
		
		if (!$result || !$products) {
			$this->log = 'Try again...';
			usleep(0.5 * 1000 * 1000);
			goto start;
		}
		
		$pages = $result['data']['trackerData']['pages'];
		$this->log_fixed['page'] .= '/' . $pages;
		
		$count_products = count($products);

		foreach ($products as $n => $product) {
			$pid = $product['id'];
			$name = $product['name'];
			$product_url = $product['product_url'];
			
			$this->log_fixed['product'] = 'Product #' . (++$n) . '/' . $count_products . ' > PID: dkp-' . $pid . '';
			//$this->log = $name;
			
			usleep(0.1 * 1000 * 1000);
			
			$product_url = str_replace('https://digikala.com', 'https://www.digikala.com', $product_url);
			
			$pos = strrpos($product_url, '/');
			$product_url_encoded = substr($product_url, 0, $pos + 1) . urlencode(substr($product_url, $pos + 1));
			
			start_product:
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $product_url_encoded);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
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
				$this->log = 'Error: ' . curl_error($ch);
				goto start_product;
			}
			curl_close($ch);
			
			if (!$result) {
				$this->log = 'Try again...';
				usleep(0.5 * 1000 * 1000);
				goto start_product;
			}
			
			$re = '/\"image\"\: \[(.*?)\],/s';
			preg_match($re, $result, $matches);
			
			if (!$matches) {
				$this->log = 'Try again...';
				usleep(0.5 * 1000 * 1000);
				goto start_product;
			}
			
			$images = json_decode('[' . $matches[1] . ']');
			$count_images = count($images);
			
			foreach ($images as $m => $image) {
			
				$filename = basename(parse_url($image, PHP_URL_PATH));
				
				$this->log_fixed['image'] = 'Img #' . (++$m) . '/' . $count_images . ' > ' . $filename . '';
				
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
					$this->log = 'Image existed... ';
					
					if ($force) {
						if ($row['hash'] == $hash) {
							$this->log = 'No changed.';
						} else {
							
							if ($sync) {
								$result = $db->prepare('UPDATE images SET hash = ? WHERE id = ?');
								if ($result->execute([$hash, $row['id']])) {
									$this->log = 'Updated.' . '(' . $filename . ')';
								} else {
									$this->log = 'Error.';
								}
							} else {
								$this->log = 'Skip.' . '(' . $filename . ')';
							}
							
							save_result($result_filename, $filename, $pid, false);
							
						}
					} else {
						$this->log = 'Ok.';
					}
					
				} else {
					$this->log = 'Found new image... ';
					
					if (!$force) {
						file_put_contents('images/' . $filename, file_get_contents($image));
						$hash = md5_file('images/' . $filename);
					}
					
					if ($sync) {
						$result = $db->prepare('INSERT INTO images(pid, filename, hash) VALUES (?, ?, ?)');
						if ($result->execute([$pid, $filename, $hash])) {
							$this->log = 'Saved.' . '(' . $filename . ')';
						} else {
							$this->log = 'Error.';
						}
					} else {
						$this->log = 'Skip.' . '(' . $filename . ')';
					}
					
					save_result($result_filename, $filename, $pid, true);
					
				}
				
			}

		}
		
    }
	
}

function save_result($result_filename, $filename, $pid, $new) {
	file_put_contents($result_filename, '<div ' . ($new ? 'style="border-color:#01f;"' : '') . '><a href="https://dkstatics-public.digikala.com/digikala-products/' . $filename . '?x-oss-process=image/resize,h_800/quality,q_80" target="_blank"><img src="https://dkstatics-public.digikala.com/digikala-products/' . $filename . '?x-oss-process=image/resize,h_150/quality,q_40"></a><br><a href="https://www.digikala.com/product/dkp-' . $pid . '" target="_blank">dkp-' . $pid . '</a></div>'."\n", FILE_APPEND);
}
