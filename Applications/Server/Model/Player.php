<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 下午3:35
 */
namespace Model;
use \Common\Constants;
use Dao\GameDao;
use Dao\PlayerDao;

class Player{

    public $uid;

    public $pid;

    public $nickName;

    public $score;

    public $registeTime;

    public $loginTime;

    public $loginIp;

    public $lastLoginIp;

    public $tableId;//当前牌局

    public $winNum;//胜利次数

    public $failNum;//失败次数

    public function __construct($data,$ip)
    {

        //时间戳＋计数器＋服务端id
//        $this->uid = (string)(time().PlayerDao::getPlayerCnt().Constants::SERVER_ID);
        $this->uid = $data['uid'];
        $this->tableId = $data['tid'];
        $this->score = $data['score'];
        $this->registeTime = time();
        $this->loginTime = time();
        $this->lastLoginIp = $ip;
        $this->loginIp = $ip;
        $this->nickName = $data['name'];
    }

    public function getPlayerInfo()
    {
        $info = array();
        $info['uid'] = $this->uid;
        $info['score'] = $this->score;
        $info['ip'] = $this->loginIp;
        $info['name'] = $this->nickName;
        return $info;
    }

    public static function getPlayerInfos($uids,$selfPlayer)
    {
        $playerInfos = array();
        foreach($uids as $uid)
        {
            if($selfPlayer->uid == $uid)
            {
                $playerInfos[$uid] = $selfPlayer->getPlayerInfo();
                continue;
            }
            $player = PlayerDao::getPlayer($uid);
            $playerInfos[$uid] = $player->getPlayerInfo();
        }
        return $playerInfos;
    }
}