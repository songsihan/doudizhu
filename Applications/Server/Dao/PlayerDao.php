<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 上午9:32
 */

namespace Dao;

use \Utils\RedisUtil;

class PlayerDao{

    const PLAYER_DATAS = 'player.datas'; //玩家数据

    const PLAYER_PID_TO_UID = 'player.uidToPid'; //玩家平台pid对应游戏中uid

    const PLAYER_CNT = 'player.cnt'; //玩家计数器-玩家编号

    /**
     * 玩家存储
     * @param $uid
     * @param $player
     */
    public static function addPlayer($uid,$player)
    {
        $uid = (int)$uid;
        return RedisUtil::hSet(self::PLAYER_DATAS,$uid,$player);
    }

    /**
     * 获取玩家数据
     * @param $uid
     */
    public static function getPlayer($uid)
    {
        $uid = (int)$uid;
        return RedisUtil::hGet(self::PLAYER_DATAS,$uid);
    }

    /**
     * 玩家唯一标识对应关系存储
     * @param $pid
     * @param $uid
     */
    public static function addPidUid($pid,$uid)
    {
        $uid = (int)$uid;
        return RedisUtil::hSet(self::PLAYER_PID_TO_UID,$pid,$uid);
    }

    /**
     * 获取玩家uid
     * @param $pid
     */
    public static function getUid($pid)
    {
        return RedisUtil::hGet(self::PLAYER_PID_TO_UID,$pid);
    }

    /**
     * 获得玩家编号
     * @return mixed
     */
    public static function getPlayerCnt()
    {
        $no = RedisUtil::incr(self::PLAYER_CNT);
        if($no >= 42949)
        {
            $no = RedisUtil::incr(self::PLAYER_CNT,-($no-1));
        }
        return $no;
    }
}
