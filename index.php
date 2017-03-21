<?php
class JSSDK {
  private $appId;
  private $appSecret;

  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();
    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("jsapi_ticket.json"));
    if ($data->expire_time < time()) {
      $accessToken = $this->getAccessToken();
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        $data->expire_time = time() + 7000;
        $data->jsapi_ticket = $ticket;
        $fp = fopen("jsapi_ticket.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("access_token.json"));
    if ($data->expire_time < time()) {
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        $fp = fopen("access_token.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  private function httpGet($url) {
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
    

    /*
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $temp = curl_exec($ch);
    curl_close($ch);
    return $temp;
    */
  }
}

$jssdk = new JSSDK("wxcac60c5221e2cc87", "b627222dbb85a7a10c541ff6420de033");
$signPackage = $jssdk->GetSignPackage();

//$fptest = fopen("./jsapi_ticket.json", "w");
//fwrite($fptest, "test"));
//fclose($fptest);

?>

<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, minimal-ui">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta name="format-detection" content="telephone=no, email=no">
    <title>Wedding Invitation 陆阳&amp;牛牛</title>
    <meta name="description" content="我们结婚啦-陆阳&amp;牛牛">
    <link rel="stylesheet" href="./css/style.css"/>
    <!--custom style-->
    <style>
        .current .title{
            -webkit-animation: slideToTop .8s ease both;
        }

        .current .subtitle{
            -webkit-animation: slideToTop .8s 0.3s ease both;
        }
    </style>
    <!--
    <script src="./js/jquery-1.9.1.min.js"></script>
    -->
    
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>

        wx.config({
            appId: '<?php echo $signPackage["appId"];?>',
            timestamp: <?php echo $signPackage["timestamp"];?>,
            nonceStr: '<?php echo $signPackage["nonceStr"];?>',
            signature: '<?php echo $signPackage["signature"];?>',
            jsApiList: [
                // 所有要调用的 API 都要加到这个列表中
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo'
            ]
        });

        /*
        wx.onMenuShareAppMessage({
            title: window.title, // 分享标题
            desc: '2017年05月01日诚挚邀请您参加我们的婚礼,共同分享我们的幸福与喜悦', // 分享描述
            link: window.location.href, // 分享链接
            imgUrl: 'http://luccalu.top/WeddingPage/img/share.jpg', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () { 
                // 用户确认分享后执行的回调函数
            },
            cancel: function () { 
                // 用户取消分享后执行的回调函数
            }
        });
        */
        
        wx.ready(function () {
            // 在这里调用 API
            var shareData = {
                title: window.title,
                desc: '2017年05月01日诚挚邀请您参加我们的婚礼,共同分享我们的幸福与喜悦',
                link: window.location.href,
                imgUrl: 'http://luccalu.top/WeddingPage/img/share.jpg'
            } 
        
            wx.onMenuShareAppMessage(shareData);
            wx.onMenuShareTimeline(shareData);
            wx.onMenuShareQQ(shareData);
            wx.onMenuShareWeibo(shareData);
        });

        wx.error(function (res) {
            alert(res.errMsg);
        });
    </script>
    
</head>
<body>
<div style='margin:0 auto;display:none;'>
<img src='./img/share.jpg' />
</div>
<div class="page-wrap">
    <div class="page">
        <div class="text_box1">
            <div class="title1">陆阳 &amp; 牛牛</div>
            <div class="subtitle1">Wedding Invitation</div>
        </div>
        

        <div class="arrow"></div>
    </div>
    <div class="page">
        <div class="title2"></div>
        <div class="subtitle2"></div>

        <div class="arrow"></div>
    </div>
    <div class="page">
        <div class="title3">page three</div>
        <div class="subtitle3">page three subtitle</div>

        <div class="arrow"></div>
    </div>
    <div class="page">
        <div class="text_box4">
            <div class="title4">结婚典礼</div>
            <div class="subtitle4">日期：2017年05月01日</div>
            <div class="subtitle4">时间：星期一 17点28分</div>
            <div class="subtitle4">礼堂时间：17点00分</div>
        </div>

        <div class="arrow"></div>
    </div>
    <div class="page">
        <div class="text_box5">
            <div class="title5">小南国花园酒店 1楼钻石D厅</div>
            <div class="subtitle5">上海市杨浦区佳木斯路777号</div>
        </div>

        <div class="arrow"></div>
    </div>
    <div class="page">
        <div class="title6">page five</div>
        <div class="subtitle6">page five subtitle</div>
    </div>
</div>

<script src="./js/zepto.min.js"></script>
<script src="./js/PageSlider.js"></script>
<script src="./js/typed.js"></script>
<script>
    var pageSliderIns = new PageSlider({
        pages: $('.page-wrap .page'),
        gestureFollowing: true,
        onchange: function () {
            Typed.new(".title2", {
                strings: ["四年前她与他相距1400公里，她在大同，他在上海，他们互不相识。</br>在众多选择中她从北京飞往了纽卡斯尔，而他从上海飞往了伦敦，那时他们相距400公里。</br>不久后他们坐在同一个教室里，那天他坐在第一排，她在最后一排。</br>时不时的遇见，偶尔修同一门课，见面时打个招呼，那时他们开始坐在隔壁座位上课了。</br>一个小作业把他们绑在了一起，她写程序，他也写程序，写累了一起去逛街，一起去买菜做饭。</br>作业做完了他们勾搭到一起了。</br></br>四年后的这一天他们终于要结婚了......"],
                //三年前她与他相拒1400公里，</br>她在大同，他在上海，他们互不相识。</br>在众多选择中她从北京飞往了纽卡斯尔，</br>而他从上海飞往了伦敦，</br>那时他们相拒400公里。</br>不久后他们坐在同一个教室里，</br>那天他坐在第一排，她在最后一排。</br>时不时的遇见，</br>偶尔修同一门课，</br>见面时打个招呼，</br>那时他们开始坐在隔壁座位上课了。</br>一个小作业把他们绑在了一起，</br>她写程序，他也写程序，</br>写累了一起去逛街，一起去买菜做饭。</br>作业做完了他们勾搭到一起了。</br></br>三年后的这一天他们终于要结婚了.......
                typeSpeed: 80,
                showCursor: false,
                contentType: "html",
                startDelay: 10
            });
        }
    });
    //document.addEventListener("DOMContentLoaded", function(){
    //document.addEventListener("pageSliderIns", function(){
        
    //});
</script>
</body>
</html>