<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 下午4:36
 */

use Api\JoinTable;
use Api\Landlord;
use Api\Play;
use Api\ChgPlaySt;
use Api\Progress;
use Common\Constants;
use Dao\PlayerDao;
use \GatewayWorker\Lib\Gateway;

use \Api\Login;
use Utils\RedisUtil;

class MsgManage{

    public static function doApi($client_id, $message)
    {
        // 获取客户端请求
        $data = json_decode($message, true);
        if(!$data)
        {
            return ;
        }

        $re = array();
        $re['type'] = $data['type'];
        $st = 0;
        if($data['type'] == 'login')
        {
            Login::doApi($data,$re);
            Gateway::bindUid($client_id,$re['uid']);
        }
        else
        {
            $uid = $data['uid'];
            $player = PlayerDao::getPlayer($uid);
//            RedisUtil::lock($uid);
            if(!$data['uid'] || !$player)
            {
                $re['s'] = Constants::RESPONSE_NO_PLAYER;
                Gateway::sendToClient($client_id, json_encode($re));
//                RedisUtil::unlock($uid);
                return;
            }
            $re['s'] = Constants::RESPONSE_SUCCESS;
            switch($data['type'])
            {
                case 'test':
                    $st = Progress::doApi($player,$data,$re,$client_id);
                    break;
                case 'jt':
                    JoinTable::doApi($player,$data,$re);
                    break;
                case 'll':
                    Landlord::doApi($player,$data,$re);
                    break;
                case 'play':
                    $st = Play::doApi($player,$data,$re);
                    break;
                case 'chgSt':
                    ChgPlaySt::doApi($player,$data,$re);
                    break;
                default:
                    $re['s'] = Constants::RESPONSE_FAIL;
                    break;
            }
        }
        if($st != 1)//为1则协议在接口内部已发送
        {
            Gateway::sendToClient($client_id, json_encode($re));
        }
//        RedisUtil::unlock($uid);
    }
}