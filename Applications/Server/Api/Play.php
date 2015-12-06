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
use Utils\CardUtil;

/**
 * playCardNos 所出的牌
 * Class Landlord
 * @package Api
 */
class Play{

    public static function doApi($player,$data,&$re)
    {
        $uid = $player->uid;
        if($player->tableId > 0)
        {
            $table = TableDao::getTable($player->tableId);
            if($table && $table->tableStatus == Constants::TABLE_IN_GAME)
            {
                if(time()-$table->recordTime >= Constants::PLAY_TIME + 2)//超时操作
                {
                    $re['s'] = Constants::RESPONSE_FAIL;
                    return 0;
                }
                if($uid == $table->currOpUid)
                {
                    $table->recordTime = time();
                    TableDao::addTable($table->tableId,$table);
                    $playCardNos = $data['playCardNos'];
                    $table->nextPlay($playCardNos,$data['op']);
                    return 1;//协议已发送
                }
            }
            else
            {
                $player->tableId = 0;
                PlayerDao::addPlayer($player->uid,$player);
            }
        }
        $re['s'] = Constants::RESPONSE_FAIL;
        return 0;
    }


    /**
     * 出牌信息
     * @param $table
     * @param $re
     * @return mixed
     */
//    public static function sendInfo($table,&$re)
//    {
//        $re['currOpUid'] = $table->currOpUid;
//        $re['tableSt'] = $table->tableStatus;
//        $re['rTime'] = $table->recordTime;
//        $re['multiple'] = $table->multiple;
//        $uids = $table->uids;
//        if($table->tableStatus == Constants::TABLE_END)
//        {
//            $re['winUid'] = $uid;
//            $table->tableOver($uid);
//        }
//
//        Gateway::sendToUid($uids,json_encode($re));
//
//    }

    /**
     * 发送清理牌局信息
     * @return mixed
     */
//    public static function sendClearTableInfo($re,$uids)
//    {
//        Gateway::sendToUid($uids,json_encode($re));
//    }

}