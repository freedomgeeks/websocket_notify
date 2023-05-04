<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Workerman/Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

// Create a Websocket server
$worker = new Worker("websocket://0.0.0.0:2346");

// 启动1个进程保证同信道
$worker->count = 1;

// 新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)
$worker->uidConnections = array();
// 进程启动后定时推送数据给客户端
$worker->onWorkerStart = function($worker){
    //将日志存入mysql
//    global $db;
//    $db = new \Workerman\MySQL\Connection('127.0.0.1', '3306', 'root', 'root', 'ws');
    //定时向客户端发数据，防止掉线
    Timer::add(10, function()use($worker){
        foreach($worker->connections as $connection) {
            $connection->send('{"type":"ping"}');
        }
    });

    //每秒导出在线用户,方便管理后台读取在线用户
    Timer::add(1, function()use($worker){
         file_put_contents('online_users.json',json_encode(array_keys($worker->uidConnections)));
    });
};
// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function($connection, $data)
{
    global $worker;
    // 判断当前客户端是否已经验证,即是否设置了uid
//    global $db;
//    //mysql包insert
//    $db->insert('aa')->cols(array(
//        'p1'=>$data,
//        'p4'=>date("Y-m-d H:i:s")))->query();
    //记录提交日志
    file_put_contents('./log/'.date('Y-m-d').'.log',date('H:i:s').$data."\n",FILE_APPEND);
    $dataArr = json_decode($data,true);
    //连接者身份
    $uid = $dataArr['uid'];
    //内容
    $message = json_encode($dataArr['data']);
    //接受者
    $recv_uid = $dataArr['to'];
    if(!isset($connection->uid))
    {
       // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
    	
       $connection->uid = $uid;
       /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
        * 实现针对特定uid推送数据
        */
       $worker->uidConnections[$connection->uid] = $connection;
    }
    // 其它逻辑，针对某个uid发送 或者 全局广播
    // uid 为 all 时是全局广播
    // 多个id广播
    if(is_array($recv_uid))
    {
    	$recv_uids = $recv_uid;
    	foreach ($recv_uids as $item) {
			sendMessageByUid($item, $message);
    	}
        
    }
    // 给全局发送
    elseif($recv_uid == 'all')
    {
    	broadcast($message);
      //单发  
    }else{
    	sendMessageByUid($recv_uid, $message);
    }
};

// 当有客户端连接断开时
$worker->onClose = function($connection)
{
    global $worker;
    if(isset($connection->uid))
    {
        // 连接断开时删除映射
        unset($worker->uidConnections[$connection->uid]);
    }
};

// 向所有验证的用户推送数据
function broadcast($message)
{
   global $worker;
   foreach($worker->uidConnections as $connection)
   {
        $connection->send($message);
   }
}

// 针对uid推送数据
function sendMessageByUid($uid, $message)
{
    global $worker;
    if(isset($worker->uidConnections[$uid]))
    {
        $connection = $worker->uidConnections[$uid];
        $connection->send($message);
    }
}

// 运行所有的worker（其实当前只定义了一个）
Worker::runAll();