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

/**
 * 叫地主/抢地主、不抢
 * op 0不抢 1抢
 * Class Landlord
 * @package Api
 */
class Landlord{

    public static function doApi($player,$data,&$re)
    {
        $uid = $player->uid;
        if($player->tableId > 0)
        {
            $table = TableDao::getTable($player->tableId);
            if($table && $table->tableStatus == Constants::TABLE_LANDLORD)
            {
//                echo "=======time:".(time()-$table->recordTime)." bool:".((time()-$table->recordTime) >= (Constants::LANDLORD_TIME + 1)).' ==========';
                if(time()-$table->recordTime >= Constants::LANDLORD_TIME + 1)//超时操作 设计时间可偏差2秒
                {
                    $re['s'] = Constants::RESPONSE_FAIL;
                    return 0;
                }
                if($uid == $table->currOpUid)
                {
                    $table->recordTime = time();
                    TableDao::addTable($table->tableId,$table);
                    $op = $data['op'];
                    if($op==0 || $op > $table->multiple || ($op == $table->multiple && $op == 1))
                    {
                        $status = $table->nextLandlord($uid,$op);
                        if($status == -3)
                        {
                            $re['s'] = Constants::RESPONSE_FAIL;
                            return 0;
                        }
                        $table->recordTime = time();
                        TableDao::addTable($table->tableId,$table);
                        $tableInfo = array();
                        if(is_array($status))
                        {
                            $tableInfo = $status['tableInfo'];
                            $status = $status['st'];
                        }
                        switch($status)
                        {
                            case Constants::LANDLORD_ENSURE:
                                $re = self::sendInfo($table,$re,$uid);
                                break;
                            case Constants::LANDLORD_RESET_TABLE:
                                $re['s'] = Constants::RESPONSE_RE_TABLE;
                                $re['tableInfo'] = $tableInfo;
                                break;
                            case Constants::LANDLORD_NEXT_CHOOSE:
                                $re = self::sendNextInfo($table,$re,$uid);
                                break;
                            case Constants::LANDLORD_RE_JOIN:
                                $re['s'] = Constants::RESPONSE_RE_JOIN;
                                break;
                        }
                        return 0;
                    }
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
     * 下一个叫地主
     * @param $table
     * @param $re
     * @return mixed
     */
    public static function sendNextInfo($table,$re,$uid = 0)
    {
        $re['s'] = Constants::RESPONSE_SUCCESS;
        $re['st'] = Constants::LANDLORD_NEXT_CHOOSE;
        $re['lUid'] = $table->landlordUid;
        $re['rTime'] = $table->recordTime;
        $re['multiple'] = $table->multiple;
        $re['lastOpUid'] = $table->lastOpUid;
        $re['currOpUid'] = $table->currOpUid;
        foreach($table->uids as $_uid)
        {
            if($_uid != $uid)
            {
                Gateway::sendToUid($_uid,json_encode($re));
            }
        }
        return $re;
    }

    /**
     * 地主确认信息
     * @param $table
     * @param $re
     * @return mixed
     */
    public static function sendInfo($table,$re,$uid = 0)
    {
        $re['s'] = Constants::RESPONSE_SUCCESS;
        $re['st'] = Constants::LANDLORD_ENSURE;
        $re['lUid'] = $table->landlordUid;
        $re['tableSt'] = $table->tableStatus;
        $re['rTime'] = $table->recordTime;
        $re['multiple'] = $table->multiple;
        foreach($table->uids as $_uid)
        {
            if($_uid != $uid)
            {
                Gateway::sendToUid($_uid,json_encode($re));
            }
        }
        return $re;
    }

}