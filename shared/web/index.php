<?php
if ( isset($_POST['networkID']) ) 
{
  $networkID = $_POST['networkID'];
  $json_str = '{"networkID":"' . $networkID . '","change":"1"}';
  echo shell_exec("echo >../configs/network_info.json");
  file_put_contents('../configs/zerotier-config.json', $json_str);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroTier - 设置面板</title>
    <link rel="shortcut icon" href="static/favicon.ico" type="image/x-icon">
    <script src="static/jquery-2.2.3.min.js" type="text/javascript"></script>
	<style>
        .bui-input {
            box-sizing: border-box;
            height: 40px;
            padding: 8px 10px;
            line-height: 24px;
            border: 1px solid #DDDDDD;
            color: #5F5F5F;
            font-size: 14px;
            vertical-align: middle;
            border-radius: 4px;
            width: 300px;
        }
        .bui-input:hover{
            border: 1px #659aea solid;
        }
        .bui-input:focus {
            outline: none;
            border: 1px solid #4F9FE9;
            box-shadow: 0 0 3px 0 #2171BB;
            color: #595959;
        }
 
        .button{
            -webkit-appearance: none;
            background: #009dff;
            border: none;
            border-radius: 2px;
            color: #fff;
            cursor: pointer;
            height: 35px;
            font-family: 'Open Sans', sans-serif;
            font-size: 1em;
            letter-spacing: 0.05em;
            text-align: center;
            text-transform: uppercase;
            transition: background 0.3s ease-in-out;
            width: 120px;
        }
        .button:hover {
            background: #00c8ff;
        }

        body{ text-align:left} 
        .div{ margin:0 auto; width:438px;}
    </style>
</head>

<body>
<div class="div">
    
<b>ZeroTier - 设置面板</b><p>
<div align="right"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="118" height="20" role="img"><linearGradient id="s" x2="0" y2="100%"><stop offset="0" stop-color="#bbb" stop-opacity=".1"/><stop offset="1" stop-opacity=".1"/></linearGradient><clipPath id="r"><rect width="118" height="20" rx="3" fill="#fff"/></clipPath><g clip-path="url(#r)"><rect width="55" height="20" fill="#555"/><rect x="55" width="63" height="20" fill="#f59400"/><rect width="118" height="20" fill="url(#s)"/></g><g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" text-rendering="geometricPrecision" font-size="110"><text x="285" y="140" transform="scale(.1)" fill="#fff" textLength="450">系统架构</text><text x="855" y="140" transform="scale(.1)" fill="#fff" textLength="450"><?php echo shell_exec("uname -m"); ?></text></g></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="118" height="20" role="img"><linearGradient id="s" x2="0" y2="100%"><stop offset="0" stop-color="#bbb" stop-opacity=".1"/><stop offset="1" stop-opacity=".1"/></linearGradient><clipPath id="r"><rect width="118" height="20" rx="3" fill="#fff"/></clipPath><g clip-path="url(#r)"><rect width="55" height="20" fill="#555"/><rect x="55" width="63" height="20" fill="#007ec6"/><rect width="118" height="20" fill="url(#s)"/></g><g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" text-rendering="geometricPrecision" font-size="110"><text x="285" y="140" transform="scale(.1)" fill="#fff" textLength="450">本地版本</text><text x="855" y="140" transform="scale(.1)" fill="#fff" textLength="450"><?php echo shell_exec("cat ../version"); ?></text></g></svg>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="https://cheen.cn/954.html" target="_blank" title="更新日志"><img src="https://img.shields.io/github/v/release/iranee/qnap-zerotier?color=2&label=%E5%9C%A8%E7%BA%BF%E7%89%88%E6%9C%AC"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><p>

<form id="zerotier_form" action="" method="post">
<div id="main">
<table border="0">
<tbody>
<tr>
<td><b>Network ID&nbsp;&nbsp;</b></td>
<td><input placeholder='网络ID' name='networkID' id="networkID" value='' type='text' size='40'  class='bui-input' /></td></tr>
</tbody>
<td colspan='2'>
<p><span id="spn_message" style="font-size: 0.9em;">网络检测中...</span></p><br />
<p align='center' >
<input type="submit" value="  保  存  " name="sub" class="button">
<input type="button" value="ZeroTier 控制中心" id="goToWebsiteButton" class="button" style="width: 180px;background-color: #ffb700;" onclick="window.open('https://my.zerotier.com/', '_blank');">
</p></td></table></div></form></div>
</body>

<script>
<?php
    echo 'var zerotier_config=';
    echo file_get_contents("../configs/zerotier-config.json");
    echo ';';
?>

$(document).ready(function() {
	    $('#zerotier_form').submit(function(e) {
        var networkID = $("#networkID").val();       
        if (networkID == "" || networkID.length == 0 || networkID == null) {
            $("#spn_message").html("Network ID 不能为空!");
            return false;
        }
        $("#spn_message").html("保存中...");
        return true;
    });
    $("#networkID").val(zerotier_config["networkID"]);
	
    var checkCount = 0;
    var $spnMessage = $("#spn_message");
    setInterval(function() {
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: 'zerotier-pid.php',
            success: function(data) {
                if (data["zerotier_pid"] === null || data["zerotier_pid"] === "") {
                    $spnMessage.html("进程未运行！");
                } else {
                    if (data["status"] === null || data["status"] === "") {
                        checkCount++;
                        $spnMessage.html("无效的网络连接，正在检测中，第" + checkCount + "次...");
                        return;
                    }
                    // 重置计数器
                    checkCount = 0;
                    var message = "PID：" + data["zerotier_pid"];
                    message += " • 版本：" + data["version"];
                    message += " • 状态：" + data["status"];
                    message += " • IP：" + data["assigned_ips"];
                    $spnMessage.html(message);
                }
            },
            error: function(xhr, textStatus, errorThrown){
                console.log('检查进程标识请求失败！');
            }
        });
    }, 5000);
});
</script>
</div>
</html>
