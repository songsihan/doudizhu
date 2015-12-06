<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/28
 * Time: 上午11:56
 */

namespace Plat;

use Config\Store;
use Utils\HttpUtil;

class LeaveGame{

    private static $url = 'yz.68wan.com:3002/game/deserter';

    public static function sendMsg($userid,$roomId)
    {
        $nowTime = time();
        $sign = md5(Store::$signKey.$nowTime.$userid.Store::$gameId.'1'.$roomId);

        $msg = array();
        $msg['userid'] = $userid;
        $msg['gameid'] = Store::$gameId;
        $msg['roomId'] = $roomId;
        $msg['gameLevel'] = 1;
        $msg['time'] = $nowTime;
        $msg['sign'] = $sign;

        list($return_code, $return_content) = HttpUtil::http_post_data(LeaveGame::$url, json_encode($msg));

        return $return_content;
    }

}