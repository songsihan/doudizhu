<?php
/**
 * Created by PhpStorm.
 * User: willis
 * Date: 15/10/8
 * Time: 下午4:06
 */
namespace Common;
class Constants{
    const SERVER_ID = 101;

//    public static $cardNos = array(
//        102,103,104,105,106,107,108,109,110,111,112,113,114,
//        202,203,204,205,206,207,208,209,210,211,212,213,214,
//        302,303,304,305,306,307,308,309,310,311,312,313,314,
//        402,403,404,405,406,407,408,409,410,411,412,413,414,
//        501,502
//    );

    //54张牌，元素 = 牌值 * 10 + 花色
    //161为小王、162为大王
    public static $cardNos = array(
        31,32,33,34,
        41,42,43,44,
        51,52,53,54,
        61,62,63,64,
        71,72,73,74,
        81,82,83,84,
        91,92,93,94,
        101,102,103,104,
        111,112,113,114,
        121,122,123,124,
        131,132,133,134,
        141,142,143,144,
        151,152,153,154,
        161,171
    );

    const NO_PALY_NUM_TO_DEPOSIT = 2;//未出牌几次则自动托管

    const TABLE_INIT_CHECK_TIME = 0.3;

    const TABLE_GAME_CHECK_TIME = 2;

    const BASE_SOCRE = 1;

    const LANDLORD_TIME = 10;

    const PLAY_TIME = 20;

    const PLAYER_UN_DEPOSIT = 1;//未托管
    const PLAYER_DEPOSIT = 2;//托管
    const PLAYER_LEAVE = -1;//已离开牌局

    const TABLE_INIT = 5;//游戏初始化
    const TABLE_LANDLORD = 1;//叫地主阶段
    const TABLE_IN_GAME = 2;//游戏中
    const TABLE_END = 3;//游戏结算

    const RESPONSE_SUCCESS = 1;//操作成功
    const RESPONSE_FAIL = -1;//操作失败
    const RESPONSE_RECONN_FAIL = -3;//重连失败
    const RESPONSE_NO_PLAYER = -2;//用户不存在
    const RESPONSE_MATCHING = 3;//匹配中
    const RESPONSE_RE_JOIN = 4;//牌局不存在重新匹配 玩家下线，重新匹配
    const RESPONSE_RE_TABLE = 5;//重置牌局
    const RESPONSE_RECONN_SUCCESS = 6;//重连成功

    //Landlord
    const LANDLORD_RESET_TABLE = 1;//重置牌局
    const LANDLORD_NEXT_CHOOSE = 2;//下一个叫地主
    const LANDLORD_ENSURE = 3;//确定地主
    const LANDLORD_RE_JOIN = 4;//重新匹配

    //CardType
    const CARD_TYPE_KING = 1;//火箭
    const CARD_TYPE_BOMB = 2;//炸弹
    const CARD_TYPE_SINGLE = 3;//单支
    const CARD_TYPE_DOUBLE = 4;//对子
    const CARD_TYPE_THREE = 5;//三条
    const CARD_TYPE_THREE_WITH = 6;//三带一手
    const CARD_TYPE_SINGLE_STRAIGHT = 7;//单顺
    const CARD_TYPE_DOUBLE_STRAIGHT = 8;//双牌straight
    const CARD_TYPE_THREE_STRAIGHT = 9;//三顺
    const CARD_TYPE_THREE_STRAIGHT_WITH = 10;//飞机带翅膀
    const CARD_TYPE_FOUR_WITH = 11;//四带二

    /**
     * 1 火箭：大小王在一起的牌型，即双王牌，此牌型最大，什么牌型都可以打。
    2 炸弹：相同点数的四张牌在一起的牌型，比如四条A。（它可以打除火箭和比它大的炸弹外的任何牌型，炸弹对炸弹时，要比大小。）
    3 单支（一手牌）：单张牌，如一支3。（按照王>A>K>Q>J>10>9>8>7>6>5>4>3的顺序出牌）
    4 对子（一手牌）：相同点数的两张牌在一起的牌型，比如55。（按照单支一样的大小顺序出牌）
    5 三条：相同点数的三张牌在一起的牌型，比如三条4。（出牌如单支）
    6 三带一手：三条 ＋ 一手牌的牌型，比如AAA+9或AAA+77。（出牌按三个的大小）
    7 单顺：五张或更多的连续单支牌组成的牌型，比如45678或345678910JQKA。2和大小王不可以连。（同样单顺时，按第一支或最后一支牌的大小出牌）
    8 双顺：三对或更多的连续对子组成的牌型，比如334455或445566778899。2和大小王不可以连。（同单顺）
    9 三顺：二个或更多的连续三条组成的牌型，比如777888或444555666777。2和大小王不可以连。（同单顺）
    10 飞机带翅膀：三顺 ＋ 同数量的一手牌，比如777888+3+6或444555666+33+77+88。（按照飞机的最小同三单的大小出牌）
    11 四带二：四条+两手牌。比如AAAA+7+9或9999+33+55。（出牌同飞机带翅膀）
     */
}