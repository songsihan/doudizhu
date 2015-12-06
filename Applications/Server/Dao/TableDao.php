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

class TableDao{

    const TABLE_INFOS = 'table.infos'; //桌子信息

    const TABLE_CNT = 'table.cnt'; //桌子计数器-桌子编号

    const TABLE_CLEAR_CNT = 'table.clearCnt';//桌子计数器清零次数

    /**
     * 桌子存储
     * @param $tid
     * @param $table
     */
    public static function addTable($tid,$table)
    {
        $tid = (int)$tid;
        return RedisUtil::hSet(self::TABLE_INFOS,$tid,$table);
    }

    /**
     * 获取桌子数据
     * @param $tid
     */
    public static function getTable($tid)
    {
        $tid = (int)$tid;
        return RedisUtil::hGet(self::TABLE_INFOS,$tid);
    }

    /**
     * 移除桌子
     * @param $tid
     * @return mixed
     */
    public static function rmTable($tid)
    {
        $tid = (int)$tid;
        return RedisUtil::hDel(self::TABLE_INFOS,$tid);
    }
    /**
     * 获得桌子编号
     * @return mixed
     */
    public static function getTableId()
    {
        $no = RedisUtil::incr(self::TABLE_CNT);
        if($no >= 4294967290)
        {
            $no = RedisUtil::incr(self::TABLE_CNT,-($no-1));
            RedisUtil::incr(self::TABLE_CLEAR_CNT);
        }
        return $no;
    }
}
