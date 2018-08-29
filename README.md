# swoft-game

* 基于swoft框架开游戏服务器框架（主要是把自己写的游戏框架，移植到swoft框架上，可以使用swoft框架的丰富组件功能）

### 一，概述

* 该框架是基于swoft框架开发的游戏框架，主要用于开发游戏服务器，简化游戏前后端开发，框架主要实现了前后端，封包解包，协议解析，压缩，粘包，路由等功能，代码示例为h5游戏。
* 框架比较简单, 把游戏框架一直到swoft框架上。
* 基本实现框架功能, 由于以前游戏框架组件没时间完善，所以移植到swoft上来。
 
### 二，示例图

![游戏demo1](images/demo1.jpg)
![游戏demo2](images/demo2.jpg)
![游戏demo3](images/demo3.png)
![游戏demo4](images/demo4.jpg)
![客户端交互测试工具](images/demo5.png)

 

### 三，特性

* 对websocket,http,tcp协议进行封装，异步处理任务
* 实现前后端二进制封包解包，采用的是msgpack扩展，msgpack对数据进行了压缩，并实现粘包处理, 服务器加入了protobuf支付，tcp有测试例子
* 数据采用固定包头，包头4个字节存包体长度，包体前2个字节分别为cmd(主命令字)，scmd(子命令字)，后面为包体内容
* 采用策略模式解耦游戏中的每个协议逻辑
* 实现定义游戏开发cmd(主命令字)和scmd(子命令字)定义，cmd和scmd主要用来路由到游戏协议逻辑处理
* 代码里有个JokerPoker类是一个款小游戏算法类，是一个示例，如示例图1,2,3,4
* 代码主要是用框架实现小游戏JokerPoker例子，服务端代码开源，客户端代码暂不开源，但是提供客户端交互测试工具，见示例图5。
* 可以方便的把JokerPoker范例去除，只使用框架功能定制开发开发自己的游戏功能


> 备注：注意此范例数字都是默认值， 未用到存储， 运行不需要数据库，数据库配置只是测试操作数据库用
        
   
### 四，环境依赖

>依赖swoft环境，请安装php扩展msgpack
    
* msgpack 

    
    
### 五，开始使用

* 1，目录说明（swoft目录不具体说明）：

```
./Game 是这个游戏服务器逻辑
``` 
         
* 2，进入bin目录，启动服务器 ：

```
./game  start   启动服务 
./game  stop    关闭服务 
./game  reload   服务重载， 优雅载入， 不需要启动服务， 可以载入所修改的逻辑

``` 

>也可以直接用php命令行启动, 但是请确认启动之前， 杀死所有相关进程

```
php run.php

``` 

* 3如果开启http服务器，其实websocket和http是同时开启的， 如果不想混在一起可以单独开启监听端口，http测试例子代码如下浏览器访问：

```
http://192.168.7.197:9503/HttpTest?aaa=bbb&ccc=ddd
http://192.168.7.197:9501/HttpTest?aaa=bbb&ccc=ddd

```

* 4如果开启了tcp服务器，测试例子在client目录下， 注意例子采用了protobuf，请安装，也可以改成msgpack，请自行修改
php执行一下就可以看到结果：

```
php tcp_client.php

```

* 3 ，H5游戏客户端代码由于公司限制，暂不开放， 但是提供了一个客户端交互测试工具，直接把client目录放入web服务器， 修改客服端配置文件配置websocket链接就能运行。

### 六，联系方式

* qq：251413215

### 七，备注

* 根目录增加docker运行环境(Dockerfile)， 可以直接直接执行下面的命令，创建镜像php_swoole, 环境增加php-protobuf支持 

```
docker build -t php_swoole .

```

