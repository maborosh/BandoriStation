<?php

require_once ROOT_PATH . '/Libraries/jieba/vendor/multi-array/MultiArray.php';
require_once ROOT_PATH . '/Libraries/jieba/vendor/multi-array/Factory/MultiArrayFactory.php';
require_once ROOT_PATH . '/Libraries/jieba/class/Jieba.php';
require_once ROOT_PATH . '/Libraries/jieba/class/Finalseg.php';

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;

Jieba::init();
Finalseg::init();

Jieba::loadUserDict(ROOT_PATH . '/Libraries/jieba/dict/bandori_room_number_dict.txt');

/**
 * @param $text
 * @param $mode 1 全模式, 2 精确模式, 3 搜索模式
 * @return array
 */
function text_segmentation($text, $mode)
{
    if ($mode == 1) {
        return Jieba::cut($text, true);
    } elseif ($mode == 2) {
        return Jieba::cut($text, false);
    } else {
        return Jieba::cutForSearch($text);
    }
}