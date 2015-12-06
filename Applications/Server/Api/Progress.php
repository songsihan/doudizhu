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

class Progress{

    /**
     * 牌局玩家进度广播
     */
    public static function doApi($player,$data,&$re)
    {
        $uid = $player->uid;
        $table = TableDao::getTable($player->tableId);
        if($table)
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