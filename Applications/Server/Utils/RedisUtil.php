<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 上午9:59
 */

namespace Utils;

use \GatewayWorker\Lib\Store;

class RedisUtil{

    const MONTH_TIME = 2592000;//30 * 86400;

    const YEAR_TIME = 31536000;//365 * 86400;

    const LOCK_PLAYERS = 'lock.players';//获得牌局成员

    public static function getStoreInstance()
    {
        return Store::instance('gateway');
    }

    /**
     * 锁过期时间为10秒-供逻辑操作
     * 获取锁的超时时间为1sec
     * @param $key
     * @return bool
     */
    public static function lock($key)
    {
        $instance = self::getStoreInstance();
        $inTime = time();
        do{
            if($instance->setnx($key,'lock'))
            {
                $instance->expire($key,3);
                return true;
            }
        }while(time()-$inTime < 1);
        return false;
    }
    public static function unlock($key)
    {
        self::delete($key);
    }

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param int $expired
     */
    public static function set($key, $value, $expired = 0)
    {
        if(!$expired)
        {
            $expired = self::YEAR_TIME;
        }
        $instance = self::getStoreInstance();
        $instance->setex($key,$expired,$value);
    }
    /**
     * 读取缓存
     * @param $key
     */
    public static function get($key)
    {
        $instance = self::getStoreInstance();
        $instance->get($key);
    }

    /**
     * 缓存自增
     *
     * @param $key
     * @param int $v
     * @return bool
     * @internal param string $key缓存key /文件名
     */
    public static function incr($key,$v = 1) {
        $instance = self::getStoreInstance();
        return $instance->incr($key,$v);
    }

    /**
     * 缓存获取
     * 不存在，设置初始值为1
     * @param $key
     * @return bool
     * @internal param string $key缓存key /文件名
     */
    public static function getNumber($key) {
        $instance = self::getStoreInstance();
        $value = $instance->get($key);
        // 没有数据
        if(!$value)
        {
            self::incr($key);
        }
        return $instance->get($key);
    }

    /**
     * 表List
     * 加入值(重复不加)
     * 返回
     */
    public static function lPushxV($key,$v)
    {
        self::lRem($key,$v);
        $len = self::lPush($key,$v);
        return $len;
    }

    /**
     * 将一个或多个值value插入到列表key的表头
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function lPush($key, $value) {
        $instance = self::getStoreInstance();
        return $instance->lPush($key, $value);
    }

    public static function lPushx($key, $value) {
        $instance = self::getStoreInstance();
        return $instance->lPushx($key, $value);
    }

    /**
     * 插入表尾
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function rPush($key, $value) {
        $instance = self::getStoreInstance();
        return $instance->rPush($key, $value);
    }

    /**
     * 移除并返回列表key的尾元素。
     * @param $key
     * @return mixed
     */
    public static function rPop($key) {
        $instance = self::getStoreInstance();
        return $instance->rPop($key);
    }

    /**
     * list缓存
     * 移出值
     */
    public static function lRem($key,$v) {
        $instance = self::getStoreInstance();
        return $instance->lRem($key, $v, 0);
    }

    /**
     * list缓存中获取指定index的值
     */
    public static function getByIndex($key,$index) {
        $instance = self::getStoreInstance();
        return $instance->lindex($key,$index);
    }

    /**
     * list缓存的长度
     */
    public static function lLen($key) {
        $instance = self::getStoreInstance();
        return $instance->llen($key);
    }

    /**
     * list缓存的获取
     */
    public static function lRange($key) {
        $instance = self::getStoreInstance();
        return $instance->lRange($key,0,-1);
    }
    /**
     * list缓存的是否存在$v
     */
    public static function lExist($key,$v) {
        $cnt = self::lRem($key,$v);
        if($cnt > 0)
        {
            return self::lPush($key,$v);
        }
        return 0;
    }

    /**
     * 删除缓存
     *
     * @param $key
     * @return bool
     * @internal param string $key缓存key /文件名
     */
    public static function delete($key) {
        $instance = self::getStoreInstance();
        return $instance->delete($key);
    }

    /**
     * hash缓存的添加
     * @param $key
     * @param $field
     * @param $value
     * @return
     */
    public static function hSet($key,$field,$value) {
        $instance = self::getStoreInstance();
        return $instance->hSet($key, $field, $value);
    }

    /**
     * hash缓存的获取
     * @param $key
     * @param $field
     * @return
     */
    public static function hGet($key,$field) {
        $instance = self::getStoreInstance();
        return $instance->hGet($key, $field);
    }

    /**
     * hash缓存的移除
     * @param $key
     * @param $field
     * @return mixed
     */
    public static function hDel($key,$field)
    {
        $instance = self::getStoreInstance();
        return  $instance->hDel( $key, $field);
    }
}