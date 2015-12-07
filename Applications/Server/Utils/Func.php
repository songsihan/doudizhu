<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 下午4:10
 */
namespace Utils;

class Func{
    //isDesc = 1 从大到小排序 isDesc = -1从小到大排序
    public static function sortSimpleCard($cardNos,$isDesc = 1)
    {
        $cnts = array();
        $vals = array();
        foreach($cardNos as $nos)
        {
            $cnts[] = count($nos);
            $vals[] = $nos[0];
        }
        $sortType = $isDesc==1?SORT_DESC:SORT_ASC;
        array_multisort($cnts, $sortType,$vals, $sortType, $cardNos);
        return $cardNos;
    }

    //对象转数组,使用get_object_vars返回对象属性组成的数组
    public static function objectToArray($obj)
    {
        $arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if(is_array($arr)){
            return array_map(__FUNCTION__, $arr);
        }else{
            return $arr;
        }
    }

    //数组转对象
    public static function arrayToObject($arr)
    {
        if(is_array($arr)){
            return (object) array_map(__FUNCTION__, $arr);
        }else{
            return $arr;
        }
    }

}