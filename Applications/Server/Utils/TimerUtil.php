<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 上午9:59
 */

namespace Utils;

use Dao\GameDao;
use Dao\TableDao;
use Workerman\Lib\Timer;
use Workerman\Worker;

class TimerUtil{

//    private static $instance;

    /**
     * 传参$param = array('args');
     * 事例1：全局函数test $tasks['test']=$param
     * 事例2：类的静态方法 Test类的run1方法
     *          $method = array('Test','run1');
     *          $tasks[$method]=$param
     * 事例3：对象方法 Test类对象的run2方法
     * $obj = new Test(); $method = array($obj,'run1');
     *          $tasks[$method]=$param
     * @var array
     */
//    private $tasks = array();

//    private $methods = array();
//    private $params = array();

//    public static function addTask($taskId,$method,$param = array())
//    {
//        $GLOBALS['method'][$taskId] = $method;
//        $GLOBALS['param'][$taskId] = $param;
//
//        var_dump($GLOBALS['method']);
//    }
//    public static function rmTask($taskId)
//    {
//        unset($GLOBALS['method'][$taskId]);
//        unset($GLOBALS['param'][$taskId]);
//    }

    public function checkTimerTask()
    {
//        $taskIds = GameDao::getTableTimers();
//        if(!$taskIds)
//        {
//            return;
//        }
//        foreach($taskIds as $taskId)
//        {
//            $obj = TableDao::getTable($taskId);
//            if($obj)
//            {
//                call_user_func_array(array($obj,'test'),array());
//            }
//        }
    }

    //启动一个进程运行定时任务
    public static function runWorker()
    {
//        $work = new Worker();
//        $work->timerUtil = new TimerUtil();
//        $work->count = 1;
//        $work->name = 'TimerWorker';
//        $work->onWorkerStart = function($work)
//        {
//            //每秒运行一次
//            Timer::add(1, array($work->timerUtil, 'checkTimerTask'));
//        };
    }
}