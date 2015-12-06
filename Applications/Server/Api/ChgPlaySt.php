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
 * 是否托管
 * op 0托管 1取消托管
 * Class Landlord
 * @package Api
 */
class ChgPlaySt{

    public static function doApi($player,$data,&$re)
    {
        $uid = $player->uid;
        if($player->tableId > 0)
        {
            $table = TableDao::getTable($player->tableId);
            if($table && $table->tableStatus == Constants::TABLE_IN_GAME)
            {
                $op = $data['op'];
                $table->playerStatus[$uid] = $op == 0?Constants::PLAYER_UN_DEPOSIT:Constants::PLAYER_DEPOSIT;
                TableDao::addTable($table->tableId,$table);
                $re['s'] = Constants::RESPONSE_SUCCESS;
                $re['st'] = $table->playerStatus[$uid];
                $re['uid']=$uid;

                Gateway::sendToUid($table->uids,json_encode($re));
                return 1;
            }
        }
        $re['s'] = Constants::RESPONSE_FAIL;
        return 0;
    }

}