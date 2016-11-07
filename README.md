##autowechat 图文自动发送平台

1.git clone https://git.coding.net/renakeji/autowechat.git

2.composer update

3.导入数据库，修改app/database.php 配置文件

4.修改app/admin/controller/Mpusers.php yourdomain.com 和 yourtoken 为你的域名和token

5.修改app/admin/cronds yourdomain.com  同4

6.修改app/index/controller/index.php index和checkuser方法的yourtoken，同4

7.添加crontab crontab -e  */5 * * * * /yourpath/app/admin/cronds   五分钟执行一次