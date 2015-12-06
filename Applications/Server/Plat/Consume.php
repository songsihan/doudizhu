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

class Consume{

    private static $url = 'yz.68wan.com:3002/pay/usegold';

    /**
     * @param $userid
     * @param $pid
     * @return mixed
     * data={
    userid  = 平台用户唯一id,
    gameid  = 游戏id,
    pid = 消耗物品列表序号,
    num = 购买数量,
    time    = 10位时间戳 超时1分钟自动失效,
    sign（32位小写） = MD5(signKey+userid+gameid+pid+num+time)
    }
     */
    public static function sendMsg($userid,$pid,$num,$time)
    {
        $sign = md5(Store::$signKey.$userid.Store::$gameId.$pid.$num,$time);

        $msg = array();
        $msg['userid'] = $userid;
        $msg['gameid'] = Store::$gameId;
        $msg['pid'] = $pid;
        $msg['num'] = $num;
        $msg['time'] = $time;
        $msg['sign'] = $sign;

        list($return_code, $return_content) = HttpUtil::http_post_data(Consume::$url, json_encode($msg));

        return $return_content;
    }

}