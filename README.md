# WindRoute
简单高效的路由，支持url匹配与url生成。

##创建路由规则


  ```
//创建路由,参数有三个
//1.是否区分大小写,默认为true
//2.是否去除路由末尾的斜线,默认为true
//3.匹配模式 Router::MAP_PATH，匹配path路由,Router::MAP_HOST,匹配主机，默认为Router::MAP_PATH
$router = new \Wind\Routing\Router(true,false);
//创建名称为home的路由规则
//路由规则中参数以xml节点的方式表示 如:<参数名 参数属性="属性值"/>或<参数名 参数属性="属性值">默认值</参数名>
//支持的参数有：
// 1.regex 参数匹配正则
// 2.pattern 参数验证方法
// 3.default 参数默认值，当参数有默认值时，在路由匹配时参数可为空
$router->map('/','home');
//创建名称为article的路由规则
$router->map('/article','article')
		->map('/<lid regex="\d+"/>','list')//在article下添加list节点,索引名称为article.list
		->map('/<aid regex="\d+"/>','show');//在article.list下增加show节点,索引名称为article.list.show
  ```

##匹配路由规则
```
//创建url
$url=new \Zend\Diactoros\Uri('http://www.site.com/article');
//匹配路由，成功返回匹配的路由节点，失败返回false
$router->match($url)->getName();//article
$router->match($url->withPath('/article/10'))->getName();//article.list
$router->match($url->withPath('/article/10/05'))->getName();//article.list.show
```

##生成url

```
//Router::url('路由索引',路由参数);
$router->url('article');// /article
$router->url('article.list',['lid'=>10]);// /article/10
$router->url('article.list.show',['lid'=>10,'aid'=>5]);// /article/10/5
```