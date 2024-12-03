# ZeroTier For QNAP with WebUI
## 介绍
ZeroTier是一款异地组网工具，能够将多个网络设备连接到一个虚拟网络中，通过局域网IP即可轻松访问所有服务。
ZeroTier builds modern, secure multi-point virtual networks, connecting peer-to-peer networks and multi-cloud mesh infrastructure.
* ZeroTier 官方开源：https://github.com/zerotier/ZeroTierOne

### v1.14.0 更新
*  增加设置选项  allow Managed 【默认开启】
 * 增加设置选项 allow DNS
 * 增加设置选项  allow Default
 * 增加设置选项  allow Global
* 优化防火墙命令，避免频繁调用
<img src="https://raw.githubusercontent.com/iranee/qnap-zerotier/refs/heads/main/New-webui.jpg" width="600"/>
## v1.12.2.3
 * 优化防火墙命令
 * 添加支持客户端bridging模式 #1

## v1.12.2.2
 * 自动添加虚拟网络设备到防火墙QuFirewall的规则
 * 关于防火墙的说明：https://cheen.cn/1321
  
## 第一版 v1.12.2.1
 * 暂时只支持加入1个Network ID
 * ARM版本受限于没有设备，无法测试。
   
##  网络状态说明
 * ACCESS_DENIED（🚫未授权）： 表示节点尝试加入网络，但被拒绝。这可能是由于网络管理员配置的限制或权限设置。
 * NOT_FOUND（🚫无效网络）： 表示尝试加入的网络不存在。可能是因为网络ID不正确或网络已经被删除。
 * OK（✅正常）： 表示成功加入网络，一切正常。
 * REQUESTING_CONFIGURATION（⌛配置中）： 表示节点正在请求配置信息。
 * AUTHORIZING（⌛授权中）： 表示节点正在进行授权过程，等待网络管理员的批准。
 * ROUTER_CANT_READ_NETWORK_CONFIG（🚫无配置）： 表示路由器无法读取网络的配置信息。
 * DENY（🚫拒绝）： 表示加入请求被拒绝。
 * OFFLINE（🚫离线）： 表示节点当前处于离线状态，可能是由于网络连接问题。

## 如何使用
在QNAP系统，通过App Center手动安装 ***.qpkg*** 后辍程序。
##### 打开WEBUI界面，输入要加入的 Networks ID，等待10秒。
* 支持 x86_64 构架的QNAP存储设备【x86_64】
* 支持 aach64 构架的QNAP存储设备 【arm_64】
* 支持 ARM 构架的QNAP存储设备 【arm-x41】

## WebUI
<img src="https://raw.githubusercontent.com/iranee/qnap-zerotier/main/logo.jpg" width="200"/>
<img src="https://raw.githubusercontent.com/iranee/qnap-zerotier/main/WebUI.jpg" width="500"/>

## 交流群
* 群名称： alist for QNAP QQ群号： 529743094
* 可以交流各种QNAP技术、技巧、问题。
<img src="https://raw.githubusercontent.com/iranee/qnap-alist-webdav/main/qq-group.jpg" alt="QQ GRPUP" width="500"/>


## Starchart
![Star History Chart](https://api.star-history.com/svg?repos=iranee/qnap-zerotier&type=Date)

## 注意事项
 * 建议路由器放行9993端口，尤其是有公网IP的用户。
 * 如果加入网络后不能访问，请尝试安装QVPN插件后重启系统
 * ~~如果加入网络后不能访问，请检查QuFirewall防火墙设置是否禁用了外国IP访问~~
   
