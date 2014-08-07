<?php

/*
modified by lhan, 
http://henryhk.github.io/
6/8/2014
*/

header("Content-type: text/html; charset=utf-8");
header("Content-type: text/html; charset=gb2312");

//get the access_token
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxdb786ca4832ec772&secret=70492c65699b55402a7d880d61f4d8f5";
$content = file_get_contents($url);
$info = json_decode($content);
define("ACCESS_TOKEN", $info->access_token);

/******************************************************************beginning of self-defined menu part*******************************************************/

//menu content json
$data = '{
    "button": [
        {
            "name": "七年概况", 
            "sub_button": [
                {
                    "type": "click", 
                    "name": "七年风采", 
                    "key": "GAIKUANG_1"
                }, 
                {
                    "type": "view", 
                    "name": "成绩查询", 
                    "url": "http://202.113.53.130:7777/zhxt_bks/zhxt_bks.html"
                }, 
                {
                    "type": "view", 
                    "name": "四六级", 
                    "url": "http://202.113.53.134/cet46/kaosheng/index.jsp"
                }, 
                {
                    "type": "click", 
                    "name": "最新通知", 
                    "key": "GAIKUANG_4"
                }
            ]
        }, 
        {
            "name": "玩转小七", 
            "sub_button": [
                {
                    "type": "click", 
                    "name": "天气", 
                    "key": "WANZHUAN_1"
                }, 
                {
                    "type": "click", 
                    "name": "导航", 
                    "key": "WANZHUAN_2"
                }, 
                {
                    "type": "click", 
                    "name": "翻译", 
                    "key": "WANZHUAN_3"
                },
                {
                    "type": "click", 
                    "name": "快递查询", 
                    "key": "WANZHUAN_4"
                }
            ]
        }, 
        {
            "name": "菜单", 
            "sub_button": [
                {
                    "type": "click", 
                    "name": "功能建议", 
                    "key": "CAIDAN_1"
                }, 
                {
                    "type": "click", 
                    "name": "加入小七", 
                    "key": "CAIDAN_2"
                },
                {
                    "type": "view", 
                    "name": "围住神经猫", 
                    "url": "http://u.ali213.net/games/shenjingcat/index.html?game_code=196&bs=wx&f=bd"
                },
                {
                    "type": "view", 
                    "name": "2048", 
                    "url": "https://github.com/gabrielecirulli/2048"
                },
                {
                	"type": "view",
                    "name": "test",
                    "url": "http://sina.com.cn/"
                }
            ]
        }
    ]
}';

//function to be called for seting up menu
function createMenu($data){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".ACCESS_TOKEN);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tmpInfo = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error'.curl_error($ch);
        }
    curl_close($ch);
    
    return $tmpInfo;
}

echo createMenu($data); //call to setup menu

/********************************************************************end of self-defined menu part**********************************************************/

define ("TOKEN","token"); //define token as "token", can be changed to any other

/**********************************************************************begining of pushing messages********************************************************/

$wechatObj = new wechatCallbackapiTest () ;

//figure out the action taken next
if (isset($_GET['echostr'])) {
   $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

//define a class for users through this api
class wechatCallbackapiTest{
     
    public function valid(){
	    $echoStr = $_GET['echostr'];
		if ($this->checkSignature()){
		    echo $echoStr;
			exit;
		}
	}
	 
	 
	 
	private function checkSignature(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = "token";
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	public function responseMsg()
	{
	   $postStr =$GLOBALS["HTTP_RAW_POST_DATA"];
	   if(!empty($postStr)){
		    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $msgType = $postObj->MsgType; //get the message type to define which kind of type to response
           
            $textTpl = "<xml>
					   <ToUserName><![CDATA[%s]]></ToUserName>
					   <FromUserName><![CDATA[%s]]></FromUserName>
					   <CreateTime>%s</CreateTime>
					   <MsgType><![CDATA[%s]]></MsgType>
					   <Content><![CDATA[%s]]></Content>
					   <FuncFlag>0</FuncFlag>
					   </xml>";
            
            //if the type if self-defined event of self-defined menu
            if ($msgType == "event" || $msgType == "EVENT"){
            	$msgEvent = $postObj->Event; //get the event type
                if ($msgEvent=="CLICK"){
                	$eventKey = $postObj->EventKey; //get the event key, which is already defined by the developer
                    switch($eventKey){
                        case "WANZHUAN_1": //key to seek weather forecast
                        	$weatherurl="http://api.map.baidu.com/telematics/v3/weather?location=天津&output=xml&ak=A1CWxEFsV6t9KQZGMdklvUTy";
							$a1=file_get_contents($weatherurl);
							$a2=simplexml_load_string($a1);
							$placeobj=$a2->results->currentCity;
							$todayobj=$a2->results->weather_data->date[0];
							$weatherobj=$a2->results->weather_data->weather[0];
							$windobj=$a2->results->weather_data->wind[0];
							$temperatureobj=$a2->results->weather_data->temperature[0];
							$contentStr="{$placeobj}{$todayobj}\n天气{$weatherobj}\n风力{$windobj}\n{$temperatureobj}";
							$msgType = "text";
                        	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        	echo $resultStr;
                        	break;
                        //more case could be added 
                    }
                }
            }else if ($keyword == "?" || $keyword=='？'){ //this part is for test api previously, mod if there are more corresponding responses
            	$msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
            	$msgType = "text";
                $contentStr = "repeat: ".$keyword."(This platform is currently debugging)";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
        }else{
        	echo "";
            exit;
        }
	}
}

/**********************************************************************end of pushing messages********************************************************/
?>