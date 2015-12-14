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
use Dao\PlayerDao;
use Dao\TableDao;
use GatewayWorker\Lib\Gateway;
use Model\Table;
use Workerman\Lib\Timer;

class JoinTable{

    /**
     * 玩家加入牌局，进入准备。
     * 1.牌局人数满足则开启牌局
     * 2.牌局人数不足且超时，牌局解散
     * @param $player
     * @param $data
     * @param $re
     * @return int
     */
    public static function doApi($player,$data,&$re)
    {
        $uid = $player->uid;
        $table = TableDao::getTable($player->tableId);
        if($table)
        {
            $table->addUid($uid);
            GameDao::addInGamePlayer($uid);
            if(count($table->playerStatus) == 3)
            {
//                echo "JoinTable:\n";
                $table->checkTime($table);
            }
            TableDao::addTable($table->tableId,$table);
            $re['s'] = Constants::RESPONSE_FAIL;
            return 0;
        }
        $re['s'] = Constants::RESPONSE_MATCHING;
        return 0;
    }


}


//$uids = $table->uids;
//$table->tableStatus = Constants::TABLE_LANDLORD;
//foreach($uids as $_uid)
//{
//    $_player = PlayerDao::getPlayer($_uid);
//    $re['tableInfo'] = $table->getTableInfo($_player);
//    Gateway::sendToUid($_uid,json_encode($re));
//}
//$table->recordTime = time();
//TableDao::addTable($table->tableId,$table);