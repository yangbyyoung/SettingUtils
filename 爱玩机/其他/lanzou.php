<?php
header("Access-Control-Allow-Origin:*");
header('Content-type:application/json; charset=utf-8');
// API调用统计
// include './sum/db/apicount.php';
// hansCount("lanzou");
// 取文件地址
(isset($_GET['url']) && $_GET['url']) ? ($uurl =  $_GET['url']) : exit(json_encode(["success" => false, "message" => "文件不存在"], JSON_UNESCAPED_UNICODE));
$_url = str_replace(['http://', 'https://'], '', $uurl);
// 取文件Key
$_keyArr = explode('/', $_url);
$_key = end($_keyArr);
// 统计基础地址
$BASEURL = 'https://_.lanzouq.com/';
// 统一接口
$_res = getData($BASEURL . $_key);
// 取参数
preg_match_all('/user-name">(.*?)<\/span>/i', $_res, $_fileAutherArr);
if ($_fileAutherArr[1]) {
  preg_match_all('/filenajax">(.*?)<\/div>/i', $_res, $_fileNameArr);
  preg_match_all('/n_file_infos">(.*?)<\/span>/i', $_res, $_fileInfoArr);
  preg_match_all('/大小：(.*?)<\/div>/i', $_res, $_fileSizeArr);
  if (count($_fileInfoArr[1]) > 1) {
    $__time = $_fileInfoArr[1][0];
    $__type = $_fileInfoArr[1][1];
  } else {
    $__time = '-';
    $__type = $_fileInfoArr[1][0];
  }
  $infoArr = [
    'name' => $_fileNameArr[1][0] ?? '-',
    'auther' => $_fileAutherArr[1][0] ?? '-',
    'time' =>   $__time ?? '-',
    'size' => $_fileSizeArr[1][0] ?? '-',
    'type' => $__type ?? '-'
  ];
} else {
  preg_match_all('/<title>(.*?) - 蓝奏云/i', $_res, $fileNameArr);
  preg_match_all('/span>(.*?)<br/i', $_res, $infoArr);
  preg_match_all('/font>(.*?)<\/font/i', $_res, $autherArr);
  $infoArr = [
    'name' => $fileNameArr[1][0] ?? '-',
    'auther' => $autherArr[1][0] ?? '-',
    'time' =>  $infoArr[1][1] ?? '-',
    'size' => $infoArr[1][0] ?? '-',
    'type' => $infoArr[1][3] ?? '-'
  ];
}

// 取二次数据
preg_match_all('/src="(.*?)" frameborder/i', $_res, $ifarmArr);
$ifarmUrl = strlen($ifarmArr[1][0]) > 36 ? $ifarmArr[1][0] : $ifarmArr[1][1];
$_sres = getData($BASEURL . $ifarmUrl);
// 取键值对
preg_match_all("/'(.*?)'/i", $_sres, $downKeyArr);
preg_match_all("/ajaxdata = '(.*?)';/i", $_sres, $ajaxdata);
preg_match_all("/websignkey = '(.*?)';/i", $_sres, $webKey);
preg_match_all("/wsk_sign = '(.*?)';/i", $_sres, $webKeys);
// 取downKey
$downKey = '';
foreach ($downKeyArr[1] as $v) {
  strlen($v) > 36 && ($downKey = $v);
}
// 取直链
if ($downKey) {
  $datas = curlPosr($downKey, $ajaxdata[1][0], $webKey[1][0] ?? $webKeys[1][0], $BASEURL);
  $json = json_decode($datas, true);
  $result = array("success" => true, 'info' => $infoArr, "download" => $json['dom'] . "/file/" . $json['url'], "fileUrl" => restoreUrl($json['dom'] . "/file/" . $json['url']));
  exit(json_encode($result, JSON_UNESCAPED_UNICODE));
} else {
  $result = array("success" => false, "message" => "文件不存在");
  exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}
// POST请求封装
function curlPosr($urls, $ajaxData, $webKey, $uname)
{
  $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
  $header[] = "CLIENT-IP:" . $ip;
  $header[] = "X-FORWARDED-FOR:" . $ip;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "{$uname}ajaxm.php");
  curl_setopt($ch, CURLOPT_REFERER, $uname . $urls);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //发送 http 报头
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
  // POST数据
  $data = array(
    "signs" => $ajaxData,
    "action" => "downprocess",
    "sign" => $urls,
    "websign" => '',
    "ves" => "1",
    "websignkey" => $webKey
  );
  curl_setopt($ch, CURLOPT_POST, 1);
  // 把post的变量加上
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}
// GET请求封装
function getData($url)
{
  $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
  $header[] = "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
  $header[] = "accept-encoding: gzip, deflate, br";
  $header[] = "accept-language: zh-CN,zh;q=0.9";
  $header[] = "cache-control: max-age=0";
  $header[] = "sec-ch-ua: \"Google Chrome\";v=\"95\", \"Chromium\";v=\"95\", \";Not A Brand\";v=\"99\"";
  $header[] = "sec-ch-ua-mobile: ?0";
  $header[] = "sec-ch-ua-platform: \"Windows\"";
  $header[] = "sec-fetch-dest: document";
  $header[] = "CLIENT-IP:" . $ip;
  $header[] = "X-FORWARDED-FOR:" . $ip;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url); //设置传输的 url
  // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //发送 http 报头
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36"); //设置UA
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // 解码压缩文件
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
  curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}
// 连接转换封装
function restoreUrl($shortUrl)
{
  $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
  $header[] = "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
  $header[] = "accept-encoding: gzip, deflate, br";
  $header[] = "accept-language: zh-CN,zh;q=0.9";
  $header[] = "cache-control: max-age=0";
  $header[] = "sec-ch-ua: \"Google Chrome\";v=\"95\", \"Chromium\";v=\"95\", \";Not A Brand\";v=\"99\"";
  $header[] = "sec-ch-ua-mobile: ?0";
  $header[] = "sec-ch-ua-platform: \"Windows\"";
  $header[] = "sec-fetch-dest: document";
  $header[] = "CLIENT-IP:" . $ip;
  $header[] = "X-FORWARDED-FOR:" . $ip;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $shortUrl);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36"); //设置UA
  curl_setopt($ch, CURLOPT_NOBODY, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_exec($ch);
  $info = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
  curl_close($ch);
  return $info;
}