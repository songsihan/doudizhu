<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/11
 * Time: 1:17
 */
namespace Model;
use Api\Landlord;
use \Common\Constants;
use Dao\GameDao;
use Dao\PlayerDao;
use Dao\TableDao;
use Dao\TimerDao;
use GatewayWorker\Lib\Gateway;
use Plat\EndGame;
use Plat\LeaveGame;
use Utils\CardPrompt;
use Utils\CardUtil;
use Workerman\Lib\Timer;

class Table{

    private function initPlayerStatus($uids)
    {
        $this->playerStatus = array();
        foreach($uids as $uid)
        {
            $this->playerStatus[$uid] = Constants::PLAYER_UN_DEPOSIT;
        }
    }

    //逆时针找下家
    public function getNextOpUid($currOpUid)
    {
        $index = array_search($currOpUid,$this->uids);
        $nextIndex = $index - 1;//逆时针出牌
        if($nextIndex < 0)
        {
            $nextIndex = 2;
        }
//        $nextIndex = $index + 1;//顺时针发牌
//        if($nextIndex > 2)
//        {
//            $nextIndex = 0;
//        }
        return $this->uids[$nextIndex];
    }

    public function resetTableInfo()
    {
        $cardInfos = CardUtil::sendCards($this->uids);
        $this->playerCards = $cardInfos['playerCards'];
        $this->threeCards = $cardInfos['threeCards'];
        $this->tableStatus = Constants::TABLE_LANDLORD;
        $this->multiple = 1;
        $this->currOpUid = array_rand($this->uids);
        $this->initPlayerStatus($this->uids);
        $this->recordTime = time();
    }

    public function getTableInfo($player)
    {
        $info = array();
        $info['tid'] = $this->tableId;
        $info['baseSc'] = Constants::BASE_SOCRE;
        $info['tableSt'] = $this->tableStatus;
        $info['multiple'] = $this->multiple;
        $info['lUid'] = $this->landlordUid;
        $info['currOpUid'] = $this->currOpUid;
        $info['lastOpUid'] = $this->lastOpUid;
        $info['lastCardNos'] = $this->lastCardNos;
        $info['playerSt'] = $this->playerStatus;
        $info['selfCardNos'] = array_values($this->playerCards[$player->uid]);
        $info['threeCards'] = $this->threeCards;
        $info['uids'] = $this->uids;
        $info['rTime'] = $this->recordTime;
        $cardNums = array();
        foreach($this->uids as $_uid)
        {
            if(!GameDao::isInGame($_uid))
            {
                //牌局建立失败
                return false;
            }
            if($player->uid != $_uid)
            {
                $cardNums[$_uid] = count($this->playerCards[$_uid]);
            }
        }
        $info['cardNums'] = $cardNums;
        $playerInfos = Player::getPlayerInfos($this->uids,$player);
        if(!$playerInfos)
        {
            return false;
        }
        $info['playerInfos'] = $playerInfos;
        return $info;
    }

    /**
     *  未对table进行存储
     * 下一个叫地主次序
     * $addMultiple 0不抢 1抢地主
     */
    public function nextLandlord($ownerUid = 0,$multiple = 0)
    {
        if($multiple)
        {

            $this->multiple = $multiple;
            $this->landlordUid = $this->currOpUid;
        }
        if($this->multiple == 3 || $this->landlordCnt == 2)
        {
            if((int)$this->landlordUid <= 0)
            {
                $this->landlordUid = $this->currOpUid;//无人抢地主，最后一个默认一分抢地主
            }
            if($this->landlordUid > 0)
            {
                $llCards = array_merge($this->playerCards[$this->landlordUid],$this->threeCards);
                rsort($llCards);
                $this->playerCards[$this->landlordUid] = $llCards;
                $this->currOpUid = $this->landlordUid;
                $this->tableStatus = Constants::TABLE_IN_GAME;
                $this->recordTime = time();
                $this->initPlayAddCd = 3;
                return Constants::LANDLORD_ENSURE;//确定地主
            }
            return -3;
        }
        else
        {
            $this->landlordCnt += 1;
            $this->lastOpUid = $this->currOpUid;
            $this->currOpUid = $this->getNextOpUid($this->currOpUid);
            $this->recordTime = time();
            return Constants::LANDLORD_NEXT_CHOOSE;//下一个叫地主
        }
    }

    /**
     *  未对table进行存储
     * 下一个出牌
     * op 1手动操作了，0未手动操作
     */
    public function nextPlay($playCardNos = array(),$op = 0)
    {
        $currOpUid = $this->currOpUid;
        $this->lastOpUid = $currOpUid;
        $this->noPlayNums[$currOpUid] = isset($this->noPlayNums[$currOpUid])?$this->noPlayNums[$currOpUid]:0;
        $this->noPlayNums[$currOpUid] = ($op== 0 ?(int)$this->noPlayNums[$currOpUid]+1:0);
        //托管，则自动出牌
        $currUidCards = $this->playerCards[$currOpUid];
        if(count($playCardNos) == 0)
        {
            if(count($this->lastCardNos) == 0)
            {
                if(CardUtil::checkCards($currUidCards))//是否是一手牌
                {
                    $playCardNos = $currUidCards;
                }
                else
                {
                    end($currUidCards);//取最小牌
                    $no = current($currUidCards);
                    $val = (int)($no/10);
                    foreach($currUidCards as $cardNo)
                    {
                        if($val == (int)($cardNo/10))
                        {
                            $playCardNos[] = $cardNo;
                        }
                    }
                }
            }
            elseif($op == 0)
            {
                $promptArrs = CardPrompt::getPromptCards($this->lastCardNos,$currUidCards);
                $promptArrs && $promptArrs[0] && $playCardNos = $promptArrs[0];
                rsort($playCardNos);
            }
        }
        $playCardData = CardUtil::checkPlayCards($this,$playCardNos);
        if($playCardData && self::isExistCards($currUidCards,$playCardNos))
        {
            //出牌成功，发送出牌信息－出牌玩家、所出的牌（无牌则为不出）
            //---客户端重置对手的手牌数量或重置自己的牌
            //是否炸弹、牌局倍数
            $re['isBomb'] = 0;
            $re['isSpring'] = 0;
            if($playCardData['type'] == Constants::CARD_TYPE_BOMB || $playCardData['type'] == Constants::CARD_TYPE_KING)
            {
                $re['isBomb'] = 1;
                $this->multiple *= 2;
            }
            $currUidCards = array_diff($currUidCards,$playCardNos);
            $this->playerCards[$currOpUid] = $currUidCards;
            $this->lastCardNos = $playCardNos;
            $this->lastPlayCardUid = $currOpUid;
            $this->currOpUid = $this->getNextOpUid($currOpUid);

            $this->currUnPlayCnt = 0;
            if(count($currUidCards) == 0)
            {
                $this->tableStatus = Constants::TABLE_END;
                $this->currOpUid = $this->lastPlayCardUid;
                if($currOpUid == $this->landlordUid)
                {
                    $isSpring = true;
                    foreach($this->playerCards as $_uid=>$cards)
                    {
                        if($_uid != $currOpUid && count($cards) < 17)
                        {
                            $isSpring = false;
                        }
                    }
                    if($isSpring)
                    {
                        $re['isSpring'] = 1;
                        $this->multiple *= 2;
                    }
                }
                //没有手牌 获胜 结算，牌局销毁－进入匹配状态
                //是否春天
            }
            $re['playCardType'] = $playCardData;//玩家所出牌的类型
        }
        else
        {
            $this->currOpUid = $this->getNextOpUid($currOpUid);
        }
        $depositUid = 'no';
        //判断是否本轮结束，本轮结束，重置上次出牌并设置上次出牌玩家为当前出牌
        if($this->currUnPlayCnt == 2)
        {
            $this->lastCardNos = array();
        }
        $this->currUnPlayCnt++;
        if($op == 0 && $this->noPlayNums[$currOpUid] >= Constants::NO_PALY_NUM_TO_DEPOSIT)
        {
            if($this->playerStatus[$currOpUid] != Constants::PLAYER_LEAVE )
            {
                $this->playerStatus[$currOpUid] = Constants::PLAYER_DEPOSIT;
            }
            //玩家未操作，状态为托管
            $depositUid = $currOpUid;
        }
        $this->recordTime = time();
        TableDao::addTable($this->tableId,$this);

        $re['type'] = 'play';
        $re['s'] = Constants::RESPONSE_SUCCESS;
        $re['leaveUid'] = $this->playerStatus[$currOpUid] == Constants::PLAYER_LEAVE?$depositUid:'no';
        $re['depositUid'] = $depositUid;//本轮托管玩家
        $re['lastCardNos'] = $this->lastCardNos;
        $re['lastPlayCardUid'] = $this->lastPlayCardUid;
        $re['currOpUid'] = $this->currOpUid;
        $re['lastOpUid'] = $this->lastOpUid;
        $re['lastOpCardNum'] = count($this->playerCards[$this->lastOpUid]);
        $re['tableSt'] = $this->tableStatus;
        $re['rTime'] = $this->recordTime;
        $re['multiple'] = $this->multiple;
        $uids = $this->uids;
        if($this->tableStatus == Constants::TABLE_END)
        {
            $re['winUid'] = $this->lastPlayCardUid;
        }
        foreach($uids as $_uid)
        {
            $_player = PlayerDao::getPlayer($_uid);
            if($_player && $_player->tableId == $this->tableId)
            {
                Gateway::sendToUid($_uid,json_encode($re));
            }
        }
        if($this->tableStatus == Constants::TABLE_END)
        {
            $this->tableOver($this->lastPlayCardUid);
        }
    }

    public static function isExistCards($selfCards,$playCards)
    {
        $arr = array_diff($playCards,$selfCards);
        return (count($arr) <= 0);
    }

    /**
     * 抢地主操作超时
     */
    public function landlordOverTime()
    {
        $status = $this->nextLandlord(0,0);
        TableDao::addTable($this->tableId,$this);
        $re['type'] = 'll';
        switch($status)
        {
            case Constants::LANDLORD_ENSURE:
                Landlord::sendInfo($this,$re);
                break;
            case Constants::LANDLORD_NEXT_CHOOSE:
                Landlord::sendNextInfo($this,$re);
                break;
            case Constants::LANDLORD_RE_JOIN:
                $this->rmTable();
                break;
        }
    }

    /**
     *  检查操作超时
     */
    public function checkTime($table = '')
    {
        $table || $table = TableDao::getTable($this->tableId);
        if(!$table || !is_object($table))
        {
//            echo "checkTime:table is not table!! table:".$table."\n";
//            echo 'flag:'.$table->blinkTimeOut."====checkTime:table is not table!! table:".$table."\n";
            return;
        }
        $nowTime = time();
        if($table->tableStatus == Constants::TABLE_INIT)
        {
//            echo "tid:".$this->tableId." checkTime:table init playerStatus:".count($table->playerStatus)." time:".time()."\n";
            if(($nowTime - $table->recordTime) >= 120 && count($table->playerStatus) < 3)//
            {
                //牌局结束-人数不足 2.初始化失败
                Table::exceptionOver($table,203,'initFail');
                $table->rmTable();
            }
            if(count($table->playerStatus) == 3)
            {
//                echo "tid:".$this->tableId." sendTableInfo\n";
                $uids = $table->uids;
                $table->tableStatus = Constants::TABLE_LANDLORD;
                $re = array();
                $re['type'] = 'jt';
                $re['s'] = Constants::RESPONSE_SUCCESS;
                foreach($uids as $_uid)
                {
                    $_player = PlayerDao::getPlayer($_uid);
                    $re['tableInfo'] = $table->getTableInfo($_player);
                    if(!$re['tableInfo'])
                    {
                        return;
                    }
                    Gateway::sendToUid($_uid,json_encode($re));
                }
                $table->recordTime = time();

                Timer::del($table->blinkTimeOut);
                $table->blinkTimeOut = Timer::add(Constants::TABLE_GAME_CHECK_TIME, array($table, 'checkTime'));
                TableDao::addTable($table->tableId,$table);
            }
        }
        elseif($table->tableStatus == Constants::TABLE_LANDLORD)
        {
//            echo "tid:".$this->tableId." checkTime:table landlord readyUids:".count($table->readyUids)." time:".time()."\n";
            if(count($table->readyUids) >= 3
                && ($nowTime - $table->recordTime) >= (Constants::LANDLORD_TIME + 1 + $table->initPlayAddCd))//11秒内允许操作，10秒的倒计时
            {
                $table->initPlayAddCd = 0;
                $table->landlordOverTime();
            }
        }
        elseif($table->tableStatus == Constants::TABLE_IN_GAME && ($nowTime - $table->recordTime) >= 2)//自动出牌间隔2秒
        {
            $status = $table->playerStatus[$table->currOpUid];

            if($status == Constants::PLAYER_UN_DEPOSIT && $nowTime-$table->recordTime < (Constants::PLAY_TIME))
            {
                return;
            }
            if($status != Constants::PLAYER_LEAVE && !GameDao::isInGame($table->currOpUid))
            {
                //玩家掉线超时或逃跑
                $table->playerStatus[$table->currOpUid] = Constants::PLAYER_LEAVE;
                LeaveGame::sendMsg($table->currOpUid,$table->tableId);
            }
            $table->nextPlay();
        }
    }

    private static function exceptionOver($table,$code,$msg)
    {

        $userInfos = array();
        foreach($table->uids as $_uid)
        {
            $userInfo = EndGame::getUserInfo($_uid,1,0,1);
            $userInfos[] = $userInfo;
        }
        EndGame::sendMsg($code,$msg,$table->tableId,$userInfos);
    }

    /**
     * 3.结算（正常结束、中途逃跑） ************************
     * 牌局结束
     * @param $uid
     */
    public function tableOver($uid)
    {
        $roomId = $this->tableId;
        $uids = $this->uids;
        $dizhuWin = ($uid == $this->landlordUid);//地主赢为true
        $userInfos = array();
        foreach($uids as $_uid)
        {
            $award = Constants::BASE_SOCRE * $this->multiple;
            $status = ($this->playerStatus[$_uid] == Constants::PLAYER_LEAVE)?1:0;
            $win = 1;
            if($_uid == $this->landlordUid)
            {
                $award *= 2;
                !$dizhuWin && $win = 0;
            }
            else
            {
                $win = $dizhuWin?0:1;
            }
            $userInfo = EndGame::getUserInfo($_uid,$status,$win,$award);
            $userInfos[] = $userInfo;
        }
        $this->rmTable();
        sleep(2);
        EndGame::sendMsg(200,'normal',$roomId,$userInfos);
    }


    /**
     * 废弃桌子
     */
    public function rmTable()
    {
        if($this->blinkTimeOut)
        {
            Timer::del($this->blinkTimeOut);
        }
        $this->blinkTimeOut = 0;
        foreach($this->uids as $uid)
        {
            $player = PlayerDao::getPlayer($uid);
            if($player && $player->tableId == $this->tableId)
            {
                PlayerDao::rmPlayer($uid);
            }
        }
        TableDao::rmTable($this->tableId);
    }

    public function addUid($uid)
    {
        if(in_array($uid,$this->uids))
        {
            $this->playerStatus[$uid] = Constants::PLAYER_UN_DEPOSIT;
            return count($this->playerStatus);
        }
        else
        {
            return false;
        }
    }

    public function __construct($tid,$uids)
    {
        $this->tableId = $tid;
        $this->uids = $uids;

        $cardInfos = CardUtil::sendCards($uids);
        $this->playerCards = $cardInfos['playerCards'];
        $this->threeCards = $cardInfos['threeCards'];
        $this->tableStatus = Constants::TABLE_INIT;
        $this->multiple = 1;
        $this->currOpUid = $uids[array_rand($uids)];
        $this->recordTime = time();
        $this->initPlayAddCd = 3;
        $this->lastCardNos = array();
        $this->initTime = time();
        $this->blinkTimeOut = Timer::add(Constants::TABLE_INIT_CHECK_TIME, array($this, 'checkTime'));
    }

    public $tableId;

    public $multiple = 1;

    public $landlordUid;

    public $currOpUid;//当前操作的玩家

    public $lastOpUid;//上次操作的玩家

    public $uids = array();//桌上的玩家

    public $threeCards = array(); //地主底牌

    public $playerCards = array(); //玩家手牌

    public $lastPlayCardUid; //上次有出牌玩家

    public $lastCardNos = array(); //上次出牌

//    public $playerUnCards = array(); 已出的牌

    public $playerStatus = array();

    public $tableStatus;

    public $bombs = array(); //炸弹2~15

    public $recordTime;      //倒计时记录�

    public $initTime;      //牌局开始时间戳�

    public $landlordCnt;     //抢地主顺序

    public $blinkTimeOut;

    public $currUnPlayCnt;

    public $resetTableCnt; //已重置牌局次数

    //初始的操作超时时间-首次为真实cd添加的秒数，
    //用于确保第一个叫地主或出牌的玩家能走完cd
    //超时操作可以晚到-界面停止，超时正常；不能晚到-提前超时，减少操作时间
    public $initPlayAddCd;

    public $noPlayNums;//未出牌次数记录

    public $readyUids = array();//已准备好的用户
}

