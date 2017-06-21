<?php

/**
 * Description: guansixu
 * User: guansixu
 * Date: 2017/6/21
 * Time: 14:14
 */
namespace Guansixu\train;

use Hanson\Vbot\Extension;
use Hanson\Vbot\Support\Http;

class Train extends AbstractMessageHandler
{
    public $author = 'guansixu';
    public $version = '1.0';
    public $name = 'train late query';
    public $zhName = '火车正晚点查询';
    private static $array = [];

    public function handler(Collection $message)
    {
        if ($message['type'] === 'text'){
            $username = $message['from']['UserName'];
            if(preg_match_all('/(?<number>[\dACDKLNTYZ]\d{1,4})(?<type>[\x{53d1}\x{5230}])(?<station>[\x{4e00}-\x{9fa5}]{2,})/ui', $message['content'], $match)){
                //正晚点查询
                $type = $match['type'][0];
                $trainNumber = $match['number'][0];
                $station = $match['station'][0];

                $url = "http://dynamic.12306.cn/mapping/kfxt/zwdcx/LCZWD/cx.jsp";

                $type = $type == "到" ? 0 : 1;
                $trainNumber = strtolower($trainNumber);
                $stationEncode = urlencode($station);
                $stationEncode = str_replace('%', '-', $stationEncode);
                $time = floor(microtime(true)*1000);
                $data = [
                    'cz' => $station,
                    'cc' => $trainNumber,
                    'cxlx' => $type,
                    'rq' => date('Y-m-d'),
                    'czEn' => $stationEncode,
                    'tp' => $time,
                ];

                if(empty($data)){
                    return "请按照：车次+到/发+站名的格式来查询，如，4481到天津";
                }

                try{
                    $url .= http_build_query($data);
                    $response = Http::get($url);
                    if(empty($response)){
                        $text = "暂时未查到信息，请稍后重试";
                    }
                    $text = mb_convert_encoding($response, 'UTF-8', 'GBK');
                    $text = str_replace(["\r", "\n", "\r\n"], "", $text);
                }catch (\Exception $e){
                    $text = "暂时未查到信息，请稍后重试";
                }
                Text::send($username, $text);
            }
        }
    }

    /**
     * 注册拓展时的操作.
     */
    public function register()
    {

    }
}