<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/10
 * Time: 上午9:59
 */

namespace Utils;

use Common\Constants;

class CardUtil{

    public static function sendCards($uids)
    {
        $playerCards = array();
        $cards = Constants::$cardNos;
        shuffle($cards);
        $index = 0;
        foreach($uids as $uid)
        {
            $arr = array_slice($cards,$index,17);
            rsort($arr);
            $playerCards[$uid] = $arr;
            $index += 17;
        }
        $info['playerCards'] = $playerCards;
        $info['threeCards'] = array_slice($cards,$index,3);
        return $info;
    }

    public static function checkPlayCards($table,$currCardNos)
    {
        $currCard = self::checkCards($currCardNos);
        if(!is_array($table->lastCardNos) || count($table->lastCardNos) == 0)
        {
            return $currCard?$currCard:false;
        }
        $lastCard = self::checkCards($table->lastCardNos);
        if($currCard && $currCard['type'] == Constants::CARD_TYPE_KING)
        {
            return $currCard;
        }
        if($currCard && $lastCard['type'] != Constants::CARD_TYPE_KING)
        {
            unset($currCard['cardNos']);
            if($currCard['type'] == Constants::CARD_TYPE_BOMB &&
                $lastCard['type'] != Constants::CARD_TYPE_BOMB)
            {
                return $currCard;
            }
            if($currCard['type'] != Constants::CARD_TYPE_BOMB &&
                $lastCard['type'] == Constants::CARD_TYPE_BOMB)
            {
                return false;
            }
            if($currCard['type'] == $lastCard['type'] && $currCard['num'] == $lastCard['num']
                && $currCard['with'] == $lastCard['with'] &&$currCard['minValue'] > $lastCard['minValue'])
            {
                return $currCard;
            }
        }
        return false;

    }

    /**
     * 检查玩家所出的卡牌是否合法
     * @param $currCardNos
     * @return bool
     */
    public static function checkCards($currCardNos)
    {
        if(!is_array($currCardNos) || count($currCardNos) == 0)
        {
            return false;
        }
        $data = self::getCardsData($currCardNos);
        if(!$data)
        {
            return;
        }
        $moreCardNos = array_diff($currCardNos,$data['cardNos']);
        $type = $data['type'];
        $cnt = count($moreCardNos);
        if($type == Constants::CARD_TYPE_KING)
        {
            if($cnt > 0)
            {
                return false;
            }
            return $data;
        }
        elseif($type == Constants::CARD_TYPE_BOMB)
        {
            if($cnt == 0)
            {
                return $data;
            }
            elseif($cnt == 2)
            {
                $data['with'] = 1;
                $data['type'] = Constants::CARD_TYPE_FOUR_WITH;
                return $data;
            }
            elseif($cnt == 4)
            {
                $data['with'] = 2;
                $data['type'] = Constants::CARD_TYPE_FOUR_WITH;
                $cardInfo = self::cardNosToNums($moreCardNos);
                $cardNums = $cardInfo['nums'];//卡牌值对应数量
                foreach($cardNums as $num)
                {
                    if($num != 2)
                    {
                        return false;
                    }
                }
                return $data;
            }
            return false;
        }
        elseif($type == Constants::CARD_TYPE_THREE)
        {
            if($cnt == 0)
            {
                return $data;
            }
            elseif($cnt == 1)
            {
                $data['with'] = 1;
                return $data;
            }
            elseif($cnt == 2)
            {
                $data['with'] = 2;
                $cardInfo = self::cardNosToNums($moreCardNos);
                $maxLen = $cardInfo['maxLen'];        //最多相同的数量
                if($maxLen != 2)
                {
                    return false;
                }
                return $data;
            }
            return false;
        }
        elseif($type == Constants::CARD_TYPE_THREE_STRAIGHT)
        {
            $num = $data['num'];
            if($cnt == 0)
            {
                return $data;
            }
            elseif($cnt == $num)
            {
                $data['with'] = 1;
                return $data;
            }
            elseif($cnt == $num * 2)
            {
                $data['with'] = 2;
                $cardInfo = self::cardNosToNums($moreCardNos);
                $cardNums = $cardInfo['nums'];//卡牌值对应数量
                foreach($cardNums as $num)
                {
                    if($num != 2)
                    {
                        return false;
                    }
                }
                return $data;
            }
            return false;
        }
        else
        {
            if($cnt == 0)
            {
                return $data;
            }
            return false;
        }
    }

    /**
     * 校验多余卡牌
     */
    public static function checkMoreCards($cnt,$moreCardNos)
    {
        if($cnt == 0 || $cnt == 2)
        {
            return true;
        }
        elseif($cnt == 4)
        {
            $cardInfo = self::cardNosToNums($moreCardNos);
            $cardNums = $cardInfo['nums'];//卡牌值对应数量
            foreach($cardNums as $num)
            {
                if($num != 2)
                {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * with 0不带 1单带 2双带
     * @param $currCardNos
     * @return array
     */
    public static function getCardsData($currCardNos)
    {
        $cnt = count($currCardNos);
        $data = array('type'=>0,'num'=>$cnt,'minValue'=>0,'cardNos'=>array(),'with'=>0);

        $cardInfo = self::cardNosToNums($currCardNos);
        $cardNums = $cardInfo['nums'];//卡牌值对应数量
        $cardValues = $cardInfo['values'];//卡牌对应的值
        $maxSameVal = $cardInfo['maxSameVal'];//最多相同的值
        $maxLen = $cardInfo['maxLen'];        //最多相同的数量

        if(isset($cardNums[16]) && isset($cardNums[17])
            && $cardNums[16] == 1&& $cardNums[17] == 1)
        {
            $data['type'] = Constants::CARD_TYPE_KING;
            $data['cardNos'] = array(161,171);
        }
        elseif($maxLen == 4)
        {
            $data['type'] = Constants::CARD_TYPE_BOMB;
            $data['minValue'] = $maxSameVal;
            $data['cardNos'] = array($maxSameVal*10+1,$maxSameVal*10+2,$maxSameVal*10+3,$maxSameVal*10+4);
        }
        elseif($maxLen == 3)
        {
            $data = self::getStraight($currCardNos,$maxSameVal,$cardValues,$maxLen,$cardNums);
            $data['type'] = $data['num'] > 1?Constants::CARD_TYPE_THREE_STRAIGHT:Constants::CARD_TYPE_THREE;
        }
        elseif($maxLen == 2)
        {
            $data = self::getStraight($currCardNos,$maxSameVal,$cardValues,$maxLen,$cardNums);
            if($data['num'] == 2)
            {
                return false;
            }
            $data['type'] = $data['num'] > 1?Constants::CARD_TYPE_DOUBLE_STRAIGHT:Constants::CARD_TYPE_DOUBLE;
        }
        else//$maxLen == 1
        {
            $data = self::getStraight($currCardNos,$maxSameVal,$cardValues,$maxLen,$cardNums);
            if($data['num'] > 1 && $data['num'] < 5)
            {
                return false;
            }
            $data['type'] = $data['num'] > 1?Constants::CARD_TYPE_SINGLE_STRAIGHT:Constants::CARD_TYPE_SINGLE;

        }

        return $data;
    }

    /**
     * @param $maxSameVal
     * @param $cardValues
     * @param $currCardNos
     * @param $straightNum
     * @param $cardNums
     * @return array
     */
    public static function getStraight($currCardNos,$maxSameVal,$cardValues,$straightNum,$cardNums)
    {
        $cardNos = array();
        $num = 0;
        $currValue = $maxSameVal;
        while(in_array($currValue,$cardValues) && $cardNums[$currValue] == $straightNum)
        {
            foreach($cardValues as $key=>$value)
            {
                if($value == $currValue)
                {
                    $cardNos[] = $currCardNos[$key];
                }
            }
            $currValue -= 1;
            $num += 1;
        }

        $data = array('type'=>0,'num'=>$num,'minValue'=>($currValue+1),'cardNos'=>$cardNos,'with'=>0);
        return $data;
    }

    public static function cardNosToNums($currCardNos)
    {
        $cardNums = array();//卡牌值对应数量
        $cardValues = array();//卡牌对应的值
        $maxLen = 0;
        $sameVal = 0;
        foreach($currCardNos as $index=>$no)
        {
            $v = (int)($no/10);
            if(isset($cardNums[$v]))
            {
                $cardNums[$v] += 1;
            }
            else
            {
                $cardNums[$v] = 1;
            }
            $cardValues[$index] = $v;
            if($maxLen < $cardNums[$v])
            {
                $maxLen = $cardNums[$v];
                $sameVal = $v;
            }
        }
        $re['nums'] = $cardNums;
        $re['values'] = $cardValues;
        $re['maxSameVal'] = $sameVal;
        $re['maxLen'] = $maxLen;
        return $re;
    }
}