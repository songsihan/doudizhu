<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 下午5:12
 */
namespace Api;

use Common\Constants;
use Dao\GameDao;
use \Dao\PlayerDao;
use Dao\TableDao;
use GatewayWorker\Lib\Gateway;
use \Model\Player;
use Model\Table;

class Login{

    /**
     * 玩家账号注册，确定牌局
     * @param $data
     * @param $re
     */
    public static function doApi($data,&$re)
    {
        $uid = $data['uid'];
        $re['uid'] = $uid;
        $player = PlayerDao::getPlayer($uid);
        if(!$player)//玩家不存在，创建新玩家
        {
            $ip = $_SERVER['REMOTE_ADDR'];
            $player = new Player($data,$ip);
            PlayerDao::addPlayer($player->uid,$player);
        }
        else
        {
            $player->lastLoginIp = $player->loginIp;
            $player->loginIp = $_SERVER['REMOTE_ADDR'];
            $player->loginTime = time();
            $player->tableId = $data['tid'];
            PlayerDao::addPlayer($player->uid,$player);
        }
        $table = TableDao::getTable($player->tableId);
        $nowTime = time();

        if($data['isReConn'] == 1)
        {
            if($table && $table->playerStatus[$uid] != Constants::PLAYER_LEAVE)
            {
                GameDao::addInGamePlayer($uid);
                $re['s'] = Constants::RESPONSE_RECONN_SUCCESS;
                $tableInfo = $table->getTableInfo($player);
                if($tableInfo)
                {
                    $re['tableInfo'] = $tableInfo;
                    Gateway::sendToUid($uid,json_encode($re));
                    return 1;
                }
            }
            GameDao::rmInGamePlayer($uid);
            $re['s'] = Constants::RESPONSE_RECONN_FAIL;
            return 0;
        }

        if(!$table || ($nowTime - $table->initTime) >= 30)
        {
            $table = new Table($data['tid'],$data['uids']);
            TableDao::addTable($table->tableId,$table);
        }
        if(in_array($uid,$table->uids))
        {
            $re['s'] = Constants::RESPONSE_SUCCESS;
        }
        else
        {
            $re['s'] = Constants::RESPONSE_FAIL;
        }
    }

}