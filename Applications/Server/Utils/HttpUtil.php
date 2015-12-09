<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/27
 * Time: 1:59
 */
namespace Utils;

class HttpUtil{
    public static function http_post_data($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('data'=>$data_string));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        echo 'code:'.$return_code."\n";
        echo 'content:'.$return_content."\n";
        echo 'msg:'.$data_string."\n";
        return array($return_code, $return_content);
    }

}

//$url = 'http://192.168.0.102/index.php?song=123';
//$data = json_encode(array());
//list($return_code, $return_content) = http_post_data($url, $data);
//echo $return_code;
//var_dump($return_content);

//echo "md5:test <br>";
//$GLOBALS['HTTP_RAW_POST_DATA']

//$url = 'yz.68wan.com:3002/game/deserter';
//$data = array();
//$data['userid'] = 1;
//$data['gameid'] = 1;
//$data['roomid'] = 1;
//$data['gameLevel'] = 1;
//$data['sign'] = $sign;

//$signKey = "NVBAsMBnBvbWVsbzESMBAGA1UE";
//$sign_str = $signKey.'1'.'1'.'1'.'1';//sign（32位小写） = MD5(signKey+userid+roomId+gameiId)
//$sign = md5($sign_str);
////$url = 'http://localhost/Test/';
//$sign = md5($signKey.'1'.'1');
//$data = array();
//$data['code'] = 200;
//$data['codeMsg'] = 'normal';
//$data['time'] = time();
//$data['roomId'] = 1;
//$data['gameId'] = 104;
//$data['gameType'] = 1;
//$data['userInfo'] = array(
//    array(
//        "userId"=>108522222222222,
//        "status"=>0,
//        "win"=>1,
//        "multiple"=> 1,
//        "integral"=> 0,
//        "rank"=> 2
//    )
//);
//$data['sign'] = $sign;
//
//$url = 'yz.68wan.com:3002/callback';
//echo json_encode($data);
//var_dump(json_decode(json_encode($data)));
//list($return_code, $return_content) = HttpUtil::http_post_data($url, json_encode($data));
//echo $return_code.'<br>';
//echo $return_content;

//$info = array();
//$info['userid'] = 1;
//$info['gameid'] = 1;
//$info['roomid'] = 1;
//$info['gameLevel'] = 1;
//$info['sign'] = $sign;
//$data['data'] = $info;