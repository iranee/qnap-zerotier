<?php
header('Content-type: application/json');

$zerotier_pid = trim(shell_exec("pidof zerotier-one"));
$zerotier_version = trim(shell_exec("zerotier-cli -v"));
$zerotier_output = shell_exec("cat ../configs/network_info.json");

if (empty($zerotier_output) || $zerotier_output === "[]") {
    $status = "";
	$assigned_ips = "🚫无连接";
} else {
    // 解析 JSON 格式的输出
    $network_data = json_decode($zerotier_output, true);
    // 提取所需的数据
    $raw_status = $network_data[0]['status'];
    // 映射状态码到状态描述
    switch ($raw_status) {
        case "ACCESS_DENIED":
            $status = "🚫未授权";
            break;
        case "NOT_FOUND":
            $status = "🚫无效网络";
            break;
        case "OK":
            $status = "✅正常";
            break;
        case "REQUESTING_CONFIGURATION":
            $status = "⌛配置中";
            break;
        case "AUTHORIZING":
            $status = "⌛授权中";
            break;
        case "ROUTER_CANT_READ_NETWORK_CONFIG":
            $status = "🚫配置失败";
            break;
        case "DENY":
            $status = "🚫拒绝";
            break;
        case "OFFLINE":
            $status = "🚫离线";
            break;
        default:
            $status = $raw_status; // 未知状态，直接使用原始状态码
            break;
    }
        // 获取IP地址部分，去除CIDR表示
        $assigned_ips_cidr = $network_data[0]['assignedAddresses'][0];
        $assigned_ips_parts = explode('/', $assigned_ips_cidr);

        // 检查IP是否为空
        if (empty($assigned_ips_parts[0])) {
            $assigned_ips = "🚫无连接";
        } else {
            $assigned_ips = $assigned_ips_parts[0];
        }
    }

// 输出 JSON
echo '{"zerotier_pid":"' . $zerotier_pid . '","status":"' . $status . '","assigned_ips":"' . $assigned_ips . '","version":"' . $zerotier_version . '"}';
?>
