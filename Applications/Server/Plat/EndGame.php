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

class EndGame{

    private static $url = 'yz.68wan.com:3002/callback';

    //返回状态码
    //200为正常结束normal
    //201玩家中途退出游戏结束leave
    //202等待玩家超时overtime
    //203初始化失败initFail(如房间数量超限)/其他可自定数值
    public static function sendMsg($code,$codeMsg,$roomId,$userInfos)
    {
    	$nowTime = time();
        $sign = md5(Store::$signKey.$nowTime.$roomId.Store::$gameId);
        $msg = array();
        $msg['code'] = $code;
        $msg['time'] = $nowTime;
        $msg['codeMsg'] = $codeMsg;
        $msg['roomId'] = $roomId;
        $msg['gameId'] = Store::$gameId;
        $msg['gameType'] = 1;
        $msg['userInfo'] = $userInfos;

        $msg['sign'] = $sign;

        list($return_code, $return_content) = HttpUtil::http_post_data(EndGame::$url, json_encode($msg));

        return $return_content;
    }

    //$winusers 胜利者ID
    public static function getUserInfo($userId,$status,$win,$multiple,$winusers = array())
    {
        $info = array();
        $info['userId'] = $userId;
        $info['status'] = $status;
        $info['win'] = $win;
        $info['multiple'] = $multiple;

        $info['integral'] = 0;
        $info['rank'] = 0;
//        if($win == 0)
//        {
//            $info['winusers'] = $winusers;
//        }

        return $info;
    }
}