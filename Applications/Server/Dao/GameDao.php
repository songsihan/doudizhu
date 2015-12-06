<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 上午9:32
 */

namespace Dao;

use Common\Constants;
use \Utils\RedisUtil;

class GameDao{

    const GAME_READY_PLAYERS = 'game.readyPlayers'; //处于匹配状态的玩家

    const GAME_IN_PLAYERS = 'game.inPlayers';       //处于游戏中的玩家

    const GAME_TIMER_TABLEID = 'game.timerTableIds';//定时任务中的牌桌id

    /**
     * 添加匹配状态玩家
     * @param $uid
     */
    public static function addReadyPlayer($uid)
    {
        $uid = (int)$uid;
        RedisUtil::lPushxV(self::GAME_READY_PLAYERS,$uid);
    }

    /**
     * 移除匹配状态玩家
     * @param $uid
     */
    public static function rmReadyPlayer($uid)
    {
        $uid = (int)$uid;
        RedisUtil::lRem(self::GAME_READY_PLAYERS,$uid);
    }

    /**
     * 获得小组成员－采用锁机制执行
     */
    public static function getGroupPlayer($playerUid)
    {
        $playerUid = (int)$playerUid;
        if(RedisUtil::lock(RedisUtil::LOCK_PLAYERS))
        {
            $playerSize = RedisUtil::lLen(self::GAME_READY_PLAYERS);
            if($playerSize >= 3)
            {
                $playerUids = array();
                //移除匹配状态
                $playerUids[] = RedisUtil::rPop(self::GAME_READY_PLAYERS);
                $playerUids[] = RedisUtil::rPop(self::GAME_READY_PLAYERS);
                self::rmReadyPlayer($playerUid);
                $playerUids[] = $playerUid;

                RedisUtil::unlock(RedisUtil::LOCK_PLAYERS);
                //添加游戏中状态
                foreach($playerUids as $_uid)
                {
                    self::addInGamePlayer($_uid);
                }

                return $playerUids;
            }
        }
        RedisUtil::unlock(RedisUtil::LOCK_PLAYERS);
        return false;
    }

    /**
     * 添加游戏中状态玩家
     * @param $uid
     */
    public static function addInGamePlayer($uid)
    {
        $uid = (int)$uid;
        RedisUtil::lPushxV(self::GAME_IN_PLAYERS,$uid);
    }

    /**
     * 移除游戏中状态玩家
     * @param $uid
     */
    public static function rmInGamePlayer($uid)
    {
        $uid = (int)$uid;
        RedisUtil::lRem(self::GAME_IN_PLAYERS,$uid);
    }

    /**
     * 玩家是否处于游戏中状态
     * @param $uid
     * @return int|mixed
     */
    public static function isInGame($uid) {
        $uid = (int)$uid;
        return RedisUtil::lExist(self::GAME_IN_PLAYERS,$uid);
    }


}
