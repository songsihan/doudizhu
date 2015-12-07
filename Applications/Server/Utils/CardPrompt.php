<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 上午9:59
 */

namespace Utils;

use Common\Constants;

class CardPrompt{

    public static function getPromptCards($lastCardNos,$cardNos)
    {
        if(!$cardNos && !$cardNos[0])
        {
            return false;
        }
        $lastCard = CardUtil::checkCards($lastCardNos);
        $lastType = $lastCard['type'];
        $minVal = $lastCard['minValue'];
        $num = $lastCard['num'];
        $_with = $lastCard['with'];

        if($lastType == Constants::CARD_TYPE_KING)
        {
            return array();
        }
        $king = array();
        $_promptArr = array();
        if(in_array(161,$cardNos) && in_array(171,$cardNos))
        {
            $king = array(161,171);
        }

        $bombData = array('type'=>Constants::CARD_TYPE_BOMB,'num'=>4,'minValue'=>2);
        $bombs = self::getPromptBaseCards($lastType == Constants::CARD_TYPE_BOMB?$lastCard:$bombData,$cardNos);
        if(count($bombs) > 0)
        {
            $_promptArr = array_merge($_promptArr,$bombs);
        }
        if(count($king) > 0)
        {
            $_promptArr[] = $king;
        }

        if($lastType == Constants::CARD_TYPE_FOUR_WITH) {
            $_lastCard = $lastCard;
            $_lastCard['type'] = Constants::CARD_TYPE_BOMB;
            $promptArr = self::getPromptBaseCards($_lastCard, $cardNos);
            $cardWith = array();
            if ($_lastCard['with'] == 1)
            {
                $singleWithData = array('type'=>Constants::CARD_TYPE_SINGLE,'num'=>1,'minValue'=>2);
                $singleWith = self::getPromptBaseCards($singleWithData,$cardNos);
                if(count($singleWith) >= 2)
                {
                    foreach($singleWith as $_single)
                    {
                        $cardWith[] = $_single[0];//客户端这里有问题
                        if(count($cardWith) == 2)
                        {
                            break;
                        }
                    }

                    if(count($cardWith) != 2)
                    {
                        return $_promptArr;
                    }
                }
                else
                {
                    return $_promptArr;
                }
            }
            elseif($_lastCard['with'] == 2)
            {
                $doubleWithData = array('type'=>Constants::CARD_TYPE_DOUBLE,'num'=>1,'minValue'=>2);
                $doubleWith = self::getPromptBaseCards($doubleWithData,$cardNos);
                if(count($doubleWith) >= 2)
                {
                    foreach($doubleWith as $_double)
                    {
                        $cardWith[] = $_double[0];
                        $cardWith[] = $_double[1];
                        if(count($cardWith) == 4)
                        {
                            break;
                        }
                    }
                    if(count($cardWith) != 4)
                    {
                        return $_promptArr;
                    }
                }
                else
                {
                    return $_promptArr;
                }
            }
            $len = count($promptArr);
            for($i = 0;$i < $len;$i++)
            {
                $promptArr[$i] = array_merge($promptArr[$i],$cardWith);
            }
        }
        elseif($lastType == Constants::CARD_TYPE_THREE_STRAIGHT_WITH)
        {
            $_lastCard = $lastCard;
            $_lastCard['type'] = Constants::CARD_TYPE_THREE_STRAIGHT;
            $promptArr = self::getPromptBaseCards($_lastCard,$cardNos);
            $cardWith = array();
            if($_lastCard['with'] === 1)
            {
                $singleWithData = array('type'=>Constants::CARD_TYPE_SINGLE,'num'=>1,'minValue'=>2);
                $singleWith = self::getPromptBaseCards($singleWithData,$cardNos);
                if(count($singleWith) >= $num)
                {
                    for($i = 0;$i < count($singleWith);$i++)
                    {
                        //确保所带的牌不是宿主牌
                        $cardWith[] = $singleWith[$i][0];
                        if(count($cardWith) == $num)
                        {
                            break;
                        }
                    }
                    if(count($cardWith) != $num)
                    {
                        return $_promptArr;
                    }
                }
                else
                {
                    return $_promptArr;
                }
            }
            elseif($lastCard['with'] == 2)
            {
                $doubleWithData = array('type'=>Constants::CARD_TYPE_DOUBLE,'num'=>1,'minValue'=>2);
                $doubleWith = self::getPromptBaseCards($doubleWithData,$cardNos);
                if(count($doubleWith) >= 2*$num)
                {
                    foreach($doubleWith as $_double)
                    {
                        $cardWith[] = $_double[0];
                        $cardWith[] = $_double[1];
                        if(count($cardWith) == 2*$num)
                        {
                            break;
                        }
                    }
                    if(count($cardWith) != 2*$num)
                    {
                        return $_promptArr;
                    }
                }
                else
                {
                    return $_promptArr;
                }
            }
            $len = count($promptArr);
            for($i = 0;$i < $len;$i++)
            {
                $promptArr[$i] = array_merge($promptArr[$i],$cardWith);
            }
        }
        elseif($lastType == Constants::CARD_TYPE_THREE && $_with > 0)
        {
            $_lastCard = $lastCard;
            $_lastCard['type'] = Constants::CARD_TYPE_THREE;
            $promptArr = self::getPromptBaseCards($_lastCard,$cardNos);
            $cardWith = array();
            if($_lastCard['with'] == 1)
            {
                $singleWithData = array('type'=>Constants::CARD_TYPE_SINGLE,'num'=>1,'minValue'=>2);
                $singleWith = self::getPromptBaseCards($singleWithData,$cardNos);
                if(count($singleWith) >= $num)
                {
                    $cardWith[] = $singleWith[0][0];
                    if(count($cardWith) != 1)
                    {
                        return $_promptArr;
                    }
                }
                else
                {
                    return $_promptArr;
                }
            }
            elseif($lastCard['with'] == 2)
            {
                $doubleWithData = array('type'=>Constants::CARD_TYPE_DOUBLE,'num'=>1,'minValue'=>2);
                $doubleWith = self::getPromptBaseCards($doubleWithData,$cardNos);
                if(count($doubleWith) >= 2)
                {
                    $cardWith[] = $doubleWith[0][0];
                    $cardWith[] = $doubleWith[0][1];
                    if(count($cardWith) != 2)
                    {
                        return $_promptArr;
                    }
                }
                else
                {
                    return $_promptArr;
                }
            }
            $len = count($promptArr);
            for($i = 0;$i < $len;$i++)
            {
                $promptArr[$i] = array_merge($promptArr[$i],$cardWith);
            }
        }
        else
        {
            $promptArr = self::getPromptBaseCards($lastCard,$cardNos);
        }

        if(count($bombs) > 0)
        {
            $promptArr = array_merge($promptArr,$bombs);
        }
        if(count($king) > 0)
        {
            $promptArr[] = $king;
        }
        return $promptArr;

    }




    /**
     *  获得提示所需的基础牌，不包含所带的牌
     */
    public static function getPromptBaseCards($lastCard,$cardNos)
    {
        $promptArr = array();

        $cardInfo = CardUtil::cardNosToNums($cardNos);
        $cardNums = $cardInfo['nums'];//卡牌值对应数量
        $valNos = $cardInfo['valNos'];//卡牌值对应卡牌编号
        $cardValues = $cardInfo['values'];//卡牌对应的值
        $maxSameVal = $cardInfo['maxSameVal'];//最多相同的值
        $maxLen = $cardInfo['maxLen'];        //最多相同的数量

        $lastType = $lastCard['type'];
        $minVal = $lastCard['minValue'];
        $num = $lastCard['num'];

        if($lastType == Constants::CARD_TYPE_BOMB)
        {
            $promptArr = CardPrompt::getSimplePrompt($minVal,$cardNums,$valNos,$promptArr,4);
        }

        if($lastType == Constants::CARD_TYPE_SINGLE)
        {
            $promptArr = CardPrompt::getSimplePrompt($minVal,$cardNums,$valNos,$promptArr,1);
        }
        else if($lastType == Constants::CARD_TYPE_DOUBLE)
        {
            $promptArr = CardPrompt::getSimplePrompt($minVal,$cardNums,$valNos,$promptArr,2);
        }
        else if($lastType == Constants::CARD_TYPE_THREE)
        {
            $promptArr = CardPrompt::getSimplePrompt($minVal,$cardNums,$valNos,$promptArr,3);
        }
        else if($lastType == Constants::CARD_TYPE_SINGLE_STRAIGHT)
        {
            $promptArr = CardPrompt::getStraightPrompt($minVal,$num,$cardNums,$valNos,$promptArr,1);
        }
        else if($lastType == Constants::CARD_TYPE_DOUBLE_STRAIGHT)
        {
            $promptArr = CardPrompt::getStraightPrompt($minVal,$num,$cardNums,$valNos,$promptArr,2);
        }
        else if($lastType == Constants::CARD_TYPE_THREE_STRAIGHT)
        {
            $promptArr = CardPrompt::getStraightPrompt($minVal,$num,$cardNums,$valNos,$promptArr,3);
        }

        return $promptArr;
    }

    public static function getSimplePrompt($minVal,$cardNums,$valNos,$promptArr,$size)
    {
        $promptNos = array();
        foreach($cardNums as $_val=>$num)
        {
            if($_val > $minVal && $num >= $size)
            {
                $promptNos[] = $valNos[$_val];
            }
        }
        $promptNos = Func::sortSimpleCard($promptNos,-1);
        foreach($promptNos as $key=>$nos)
        {
            if(count($nos) > $size)
            {
                array_splice($nos,0,count($nos)-$size);
            }
            $promptArr[] = $nos;
        }
        return $promptArr;
    }

    /**
     * 简单牌提示：单牌 对牌 三张 四张 size一次为1,2,3,4
     */
    public static function getSimplePrompt_old($minVal,$cardNums,$valNos,$promptArr,$size)
    {
        foreach($cardNums as $_val=>$num)
        {
            if($_val > $minVal && $num > ($size-1))
            {
                $nos = $valNos[$_val];
                if(count($nos) > $size)
                {
                    array_splice($nos,0,$size-count($nos));
                }
                $promptArr[] = $nos;
            }
        }
        return $promptArr;
    }
    /**
     * size 1为单顺 2为双顺 3为三顺
     */
    public static function getStraightPrompt($minVal,$num,$cardNums,$valNos,$promptArr,$size)
    {
        $startVal = $minVal<=2?2:$minVal;
        $endVal = 15 - ($num<=5?5:$num);
        while($startVal+1 <= $endVal)
        {
            $nos = array();
            for($_val = $startVal+1;$_val < 15;$_val++)
            {
                if(isset($cardNums[$_val]) && (int)$cardNums[$_val] >= $size)
                {
                    $_nos = $valNos[$_val];
                    for($i = 0;$i < $size;$i++)
                    {
                        $nos[] = $_nos[$i];
                    }
                    if(count($nos) == $num*$size)
                    {
                        $promptArr[] = $nos;
                        $startVal = $startVal + 1;
                        break;
                    }
                }
                else
                {
                    $startVal = $_val;
                    break;
                }
            }
        }
        return $promptArr;
    }
}