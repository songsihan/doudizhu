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
     * @return int
     */
    public static function doApi($data,&$re)
    {
        if(!isset($data['uid']))
        {
            $re['s'] = Constants::RESPONSE_FAIL;
            return 0;
        }
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

        if($table && isset($table->playerStatus[$uid])
            && ($nowTime - $table->recordTime) <= 60 //记录时间一分钟未更新，牌局失效
            && $table->tableStatus != Constants::TABLE_INIT
            && $table->tableStatus != Constants::TABLE_END
            && $table->playerStatus[$uid] != Constants::PLAYER_LEAVE)
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

        if(!$table || ($nowTime - $table->initTime) >= 120)
        {
            $table = new Table($data['tid'],$data['uids']);
            TableDao::addTable($table->tableId,$table);
        }
        if($table && in_array($uid,$table->uids))
        {
            $re['s'] = Constants::RESPONSE_SUCCESS;
        }
        else
        {
            $re['s'] = Constants::RESPONSE_FAIL;
        }
    }

}