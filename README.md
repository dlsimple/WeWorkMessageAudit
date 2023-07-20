# WeWorkMessageAudit
企业微信会话存档, 基于企业微信C版官方SDK封装的php7的扩展，在laravel 8 下实现schedule，来定时抓取企业微信会话并存到数据库内，暂时只支持在`linux`环境下使用。

### 官方文档地址
https://open.work.weixin.qq.com/api/doc/90000/90135/91774

### 使用方式
#### 设置数据库
> git clone git@github.com:dlsimple/WeWorkMessageAudit.git
> 
> cd WeWorkMessageAudit
>
> copy .env.example .env

在.env 里面填写DB的链接信息，然后

> php artisan migrate

#### 设置企业微信
在.env下增加配置：
> WXWORK_CORP_ID=
> 
> WXWORK_SECRET=
>
> WXWORK_PRIVATE_KEY=
>
> WXWORK_DATA_LIMIT= 每次拉去的数据条数


WXWORK_PRIVATE_KEY 指的是保存wxwork msgaudit 申请的私钥

#### 搭建docker运行环境
> cd messageaudit
>
> docker build -t xlogical:wework-message-audit  .
>
> docker-compose up -d


运行成功后，系统会自动运行 php artisan schedule:work 每小时一次
