**基于workerman的websocket服务端**
可用于用户扫码反馈等。
php main.php start 启动
- 简易一对一通讯，适合小型项目，也可全局推送to填all,多人填id数组，具体根据业务修改对应代码
![输入图片说明](https://images.gitee.com/uploads/images/2020/0113/152459_72253755_1423142.png "企业微信截图_20200113152343.png")

示例：
Windows下 php main.php 启动websocket

双击打开ws_test.html页面

运行 php msg_push.php 向页面发送数据


![输入图片说明](https://images.gitee.com/uploads/images/2020/0821/175239_33016e55_1423142.png "屏幕截图.png")