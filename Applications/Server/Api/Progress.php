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
use Workerman\Lib\Timer;

class Progress{

    /**
     * 牌局玩家进度广播
     */
    public static function doApi($player,$data,&$re,$client_id)
    {
        $uid = $player->uid;
        $table = TableDao::getTable($player->tableId);
        if(!$table)
        {
            return 1;
        }
        if(!Timer::isExistTimer($table->blinkTimeOut))
        {
            Gateway::bindUid($client_id,$uid);
            $table->blinkTimeOut = Timer::add(Constants::TABLE_INIT_CHECK_TIME, array($table, 'checkTime'));
            TableDao::addTable($table->tableId,$table);
        }
        if(!isset($table->playerStatus[$uid]))
        {
            $table->addUid($uid);
            GameDao::addInGamePlayer($uid);
            TableDao::addTable($table->tableId,$table);
        }
        if($data['st'] == 1)
        {
            if(!in_array($uid,$table->readyUids))
            {
                $table->readyUids[] = $uid;
                TableDao::addTable($table->tableId,$table);
            }
            if(count($table->readyUids) >= 3)
            {
                $table->recordTime = time();
                TableDao::addTable($table->tableId,$table);
                $re['uid'] = -1;
                Gateway::sendToUid($table->uids,json_encode($re));
            }
        }
        if($data['addVal'] != -1)//添加值为-1 表示该请求为心跳
        {
            $uids = $table->uids;
            $re['uid'] = $uid;
            foreach($uids as $_uid)
            {
                if($_uid == $uid)
                {
                    continue;
                }
                $re['addVal'] = $data['addVal'];
                $re['oldVal'] = $data['oldVal'];
                Gateway::sendToUid($_uid,json_encode($re));
            }
        }
        return 1;

    }

}