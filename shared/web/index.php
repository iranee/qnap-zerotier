<?php
session_start();
// 验证 QNAP 登录状态
if (!isset($_COOKIE['NAS_SID']) || empty($_COOKIE['NAS_SID'])) {
    http_response_code(401);
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>需要登录 - ZeroTier</title>
        <link rel="shortcut icon" href="static/favicon.ico" type="image/x-icon">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .auth-container {
                background: white;
                padding: 50px 40px;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 450px;
                width: 90%;
            }
            .lock-icon {
                font-size: 64px;
                margin-bottom: 20px;
                animation: shake 0.5s ease-in-out;
            }
            @keyframes shake {
                0%, 100% { transform: rotate(0deg); }
                25% { transform: rotate(-10deg); }
                75% { transform: rotate(10deg); }
            }
            h1 {
                color: #333;
                font-size: 28px;
                margin-bottom: 10px;
            }
            .subtitle {
                color: #666;
                font-size: 14px;
                margin-bottom: 30px;
            }
            .message {
                background: #fff3cd;
                border: 1px solid #ffc107;
                color: #856404;
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 25px;
                font-size: 14px;
                line-height: 1.6;
            }
            .btn-login {
                display: inline-block;
                background: #009dff;
                color: white;
                padding: 14px 40px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 16px;
                font-weight: bold;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 157, 255, 0.3);
            }
            .btn-login:hover {
                background: #00c8ff;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 157, 255, 0.4);
            }
            .footer-info {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e0e0e0;
                color: #999;
                font-size: 12px;
            }
            .countdown {
                color: #009dff;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="lock-icon">🔒</div>
            <h1>需要登录 QNAP</h1>
            <br />
            <div class="message">
                ⚠️ 需要先登录 QNAP NAS 系统<br><br>才能访问 ZeroTier 配置界面
            </div>
            
            <a href="/" class="btn-login" id="loginBtn">前往 QNAP 登录</a>
            
            <div class="footer-info">
                <span class="countdown" id="countdown">10</span> 秒后自动跳转到登录页面
            </div>
        </div>
        
        <script>
            let seconds = 10;
            const countdownEl = document.getElementById('countdown');
            const loginBtn = document.getElementById('loginBtn');
            
            const timer = setInterval(function() {
                seconds--;
                countdownEl.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.top.location.href = '/';
                }
            }, 1000);
            
            loginBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearInterval(timer);
                window.top.location.href = '/';
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

function verifyQNAPSession() {
    $sid = $_COOKIE['NAS_SID'];
    
    $check_cmd = "/sbin/qweb sid_check '$sid' 2>/dev/null";
    $result = shell_exec($check_cmd);
    
    if (strpos($result, 'OK') !== false) {
        return true;
    }
    
    return false;
}

$zerotierConfigPath = __DIR__ . '/../configs/zerotier-config.json';
if (file_exists($zerotierConfigPath)) {
    $zerotierConfig = file_get_contents($zerotierConfigPath);
    $zerotierConfigData = json_decode($zerotierConfig, true);

    if ($zerotierConfigData !== null && is_array($zerotierConfigData)) {
        $configValues = array(
            'networkID' => isset($zerotierConfigData['networkID']) ? $zerotierConfigData['networkID'] : '',
            'allowManaged' => isset($zerotierConfigData['allowManaged']) ? $zerotierConfigData['allowManaged'] : 1,
            'allowGlobal' => isset($zerotierConfigData['allowGlobal']) ? $zerotierConfigData['allowGlobal'] : 0,
            'allowDefault' => isset($zerotierConfigData['allowDefault']) ? $zerotierConfigData['allowDefault'] : 0,
            'allowDNS' => isset($zerotierConfigData['allowDNS']) ? $zerotierConfigData['allowDNS'] : 0
        );
    } else {
        $configValues = array(
            'networkID' => '',
            'allowManaged' => 1,
            'allowGlobal' => 0,
            'allowDefault' => 0,
            'allowDNS' => 0
        );
    }
} else {
    $configValues = array(
        'networkID' => '',
        'allowManaged' => 1,
        'allowGlobal' => 0,
        'allowDefault' => 0,
        'allowDNS' => 0
    );
}

if (isset($_POST['networkID'])) {
    $networkID = $_POST['networkID'];
    $configValues['networkID'] = $networkID;
    $json_str = json_encode(array('networkID' => $networkID, 'change' => "1"));
    file_put_contents($zerotierConfigPath, $json_str);
    $saved = "已保存，";
}

if (isset($_POST['allowManaged']) && isset($_POST['allowGlobal']) && isset($_POST['allowDefault']) && isset($_POST['allowDNS'])) {
    $allowManaged = $_POST['allowManaged'];
    $allowGlobal = $_POST['allowGlobal'];
    $allowDefault = $_POST['allowDefault'];
    $allowDNS = $_POST['allowDNS'];
    $configValues['allowManaged'] = $allowManaged;
    $configValues['allowGlobal'] = $allowGlobal;
    $configValues['allowDefault'] = $allowDefault;
    $configValues['allowDNS'] = $allowDNS;

    $json_str = json_encode(array(
        'networkID' => $configValues['networkID'],
        'allowManaged' => $allowManaged,
        'allowGlobal' => $allowGlobal,
        'allowDefault' => $allowDefault,
        'allowDNS' => $allowDNS,
        'change' => "1"
    ));
    file_put_contents($zerotierConfigPath, $json_str);
    $saved = "配置已更新，";
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
        body {
            user-select: none;
            font-family: Arial, sans-serif;
            font-size: 16px;
        }
        .div {
            margin: 0 auto;
            width: 400px;
            padding: 20px;
        }
        .bui-input {
            box-sizing: border-box;
            height: 36px;
            padding: 8px 10px;
            line-height: 20px;
            border: 1px solid #DDDDDD;
            color: #5F5F5F;
            font-size: 18px;
            vertical-align: middle;
            border-radius: 4px;
            width: 300px;
            font-family: monospace;
        }
        .bui-input:hover {
            border: 1px #659aea solid;
        }
        .bui-input:focus {
            outline: none;
            border: 1px solid #4F9FE9;
            box-shadow: 0 0 3px 0 #2171BB;
            color: #595959;
        }
        
.button {
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

/* 保存按钮 - 加宽 */
.button-save {
    width: 180px;
    background: #009dff;
}

.button-save:hover {
    background: #00c8ff;
}

.button-classic {
    width: 160px;
    background: #ff9800;
}
.button-classic:hover {
    background: #ffb700;
}

.button-central {
    width: 160px;
    background: #4caf50;
}

.button-central:hover {
    background: #66bb6a;
}
        
       .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
            background-color: #f8f9fa;
        }
        .checkbox-container:hover {
            background-color: #f0f0f0;
        }
        .checkbox-label {
            margin-left: 8px;
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
        }
        
        .form-group {
            margin: 15px 0;
        }
        
        .network-id-container {
            margin-bottom: 20px;
        }
        
        .status-message {
            margin: 15px 0;
            padding: 8px;
            border-radius: 4px;
            background-color: #f8f9fa;
            color: #666;
            font-size: 13px;
        }
        
        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .secondary-button {
            background-color: #ffb700;
            width: 180px;
        }
        .secondary-button:hover {
            background-color: #ffc730;
        }
        
        table {
            width: 100%;
        }
        
        td {
            padding: 8px 0;
        }
        
        h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="div">
<div style="min-height: 80px;">
<b>ZeroTier - 设置面板</b><p>
<div align="right"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="118" height="20" role="img"><linearGradient id="s" x2="0" y2="100%"><stop offset="0" stop-color="#bbb" stop-opacity=".1"></stop><stop offset="1" stop-opacity=".1"></stop></linearGradient><clipPath id="r"><rect width="118" height="20" rx="3" fill="#fff"></rect></clipPath><g clip-path="url(#r)"><rect width="55" height="20" fill="#555"></rect><rect x="55" width="63" height="20" fill="#f59400"></rect><rect width="118" height="20" fill="url(#s)"></rect></g><g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" text-rendering="geometricPrecision" font-size="110"><text x="285" y="140" transform="scale(.1)" fill="#fff" textLength="450">系统架构</text><text x="855" y="140" transform="scale(.1)" fill="#fff" textLength="450"><?php echo shell_exec("uname -m"); ?></text></g></svg>&nbsp;&nbsp;&nbsp;
<span style="display: inline-block; background: #555; border-radius: 3px; font-size: 11px; line-height: 20px; height: 20px; font-family: Verdana, Geneva, sans-serif; overflow: hidden;">
    <span style="display: inline-block; background: #555; color: #fff; padding: 0 7px;">本地版本</span>
    <span style="display: inline-block; background: #007ec6; color: #fff; padding: 0 7px;" id="zerotier_version">0.00.0</span>
</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="https://cheen.cn/1302" target="_blank" title="更新日志"><img src="https://img.shields.io/github/v/release/iranee/qnap-zerotier?color=2&amp;label=%E5%9C%A8%E7%BA%BF%E7%89%88%E6%9C%AC"></a>&nbsp;&nbsp;&nbsp;</div>
</div>
    <form id="zerotier_form" action="" method="post">
        <div id="main">
            <div class="network-id-container">
                <table>
                    <tr>
                        <td width="100"><b>Network ID</b></td>
                        <td>
                            <input placeholder='网络ID' name='networkID' id="networkID" value='' 
                                   type='password' class='bui-input' 
                                   onmouseover="showText('networkID')" 
                                   onmouseout="hideText('networkID')" 
                                   data-original="" autocomplete="off" />
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="form-group">
                <div class="checkbox-grid">
                    <div class="checkbox-container">
                        <input type="checkbox" name="allowManaged" id="allowManaged">
                        <label for="allowManaged" class="checkbox-label">Allow Managed</label>
                    </div>
                    
                    <div class="checkbox-container">
                        <input type="checkbox" name="allowGlobal" id="allowGlobal">
                        <label for="allowGlobal" class="checkbox-label">Allow Global</label>
                    </div>
                    
                    <div class="checkbox-container">
                        <input type="checkbox" name="allowDefault" id="allowDefault">
                        <label for="allowDefault" class="checkbox-label">Allow Default</label>
                    </div>
                    
                    <div class="checkbox-container">
                        <input type="checkbox" name="allowDNS" id="allowDNS">
                        <label for="allowDNS" class="checkbox-label">Allow DNS</label>
                    </div>
                </div>
            </div>
            
            <div class="status-message">
                <span id="spn_message">网络检测中...</span>
            </div>
            
<div class="button-container">
    <input type="submit" value="保  存" name="sub" class="button button-save">
    <input type="button" value="控制中心(经典)" 
           class="button button-classic"
           onclick="window.open('https://my.zerotier.com/', '_blank');">
    <input type="button" value="控制中心(新版)" 
           class="button button-central"
           onclick="window.open('https://central.zerotier.com/', '_blank');">
</div>
        </div>
    </form>
</div>
<script>
var zerotier_config = <?php echo json_encode($configValues); ?>;
$(document).ready(function() {
    $('#zerotier_form').submit(function(e) {
        e.preventDefault(); // 阻止表单默认提交行为
        var networkID = $("#networkID").val();
        var allowManaged = $("#allowManaged").is(":checked") ? 1 : 0;
        var allowGlobal = $("#allowGlobal").is(":checked") ? 1 : 0;
        var allowDefault = $("#allowDefault").is(":checked") ? 1 : 0;
        var allowDNS = $("#allowDNS").is(":checked") ? 1 : 0;
        var configData = {
            networkID: networkID,
            allowManaged: allowManaged,
            allowGlobal: allowGlobal,
            allowDefault: allowDefault,
            allowDNS: allowDNS
        };
        $.ajax({
            type: "POST",
            url: "",
            data: configData,
            success: function(response) {
                $("#spn_message").html("⌛ 配置已保存，服务重启中...");
            },
            error: function() {
                $("#spn_message").html("保存失败");
            }
        });
    });
    // 设置复选框的状态
    $("#allowManaged").prop('checked', zerotier_config.allowManaged == 1);
    $("#allowGlobal").prop('checked', zerotier_config.allowGlobal == 1);
    $("#allowDefault").prop('checked', zerotier_config.allowDefault == 1);
    $("#allowDNS").prop('checked', zerotier_config.allowDNS == 1);
    // 设置 Network ID 文本框的值
    $("#networkID").val(zerotier_config.networkID || '');
    
    var checkCount = 0;
    var consecutiveFailCount = 0;
    var $spnMessage = $("#spn_message");
    
    setInterval(function() {
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: 'zerotier-pid.php',
            success: function(data) {
                if (data["zerotier_pid"] === null || data["zerotier_pid"] === "") {
                    consecutiveFailCount++;
                                        if (consecutiveFailCount >= 3) {
                        $spnMessage.html("未检测到进程，请重启插件！");
                    } else {
                        $spnMessage.html("⌛ 服务重启中，请稍候...（" + (consecutiveFailCount * 5) + "秒）");
                    }
                } else {
                    consecutiveFailCount = 0;
                    
                    if (data["status"] === null || data["status"] === "") {
                        checkCount++;
                        $spnMessage.html("无效的网络连接，正在检测中，第" + checkCount + "次...");
                        return;
                    }
                    checkCount = 0;
                    var message = "PID：" + data["zerotier_pid"];
                    message += " • 版本：" + data["version"];
                    message += " • 状态：" + data["status"];
                    message += " • IP：" + data["assigned_ips"];
                    $spnMessage.html(message);
                    $("#zerotier_version").text(data["version"]);
                }
            },
            error: function(xhr, textStatus, errorThrown){
                console.log('进程标识请求失败！');
            }
        });
    }, 5000);
});

// 禁止整个页面右键菜单
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// 允许文本框内右键菜单
document.addEventListener('DOMContentLoaded', function() {
    var textFields = document.querySelectorAll('input[type="text"], input[type="password"]');
    textFields.forEach(function(field) {
        field.addEventListener('contextmenu', function(e) {
            e.stopPropagation();
        });
    });
});

// 切换密码框显示状态
function togglePassword(id) {
    var element = document.getElementById(id);
    element.type = (element.type === "password") ? "text" : "password";
}
function showText(id) {
    var element = document.getElementById(id);
    element.type = "text";
}
function hideText(id) {
    var element = document.getElementById(id);
    element.type = "password";
}
</script>

</body>
</html>