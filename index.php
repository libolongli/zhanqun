<?php
	
	
	function getBaseDomain($url=''){
        if(!$url){
            return $url;
        }
        #列举域名中固定元素
        $state_domain = array(
            'al','dz','af','ar','ae','aw','om','az','eg','et','ie','ee','ad','ao','ai','ag','at','au','mo','bb','pg','bs','pk','py','ps','bh','pa','br','by','bm','bg','mp','bj','be','is','pr','ba','pl','bo','bz','bw','bt','bf','bi','bv','kp','gq','dk','de','tl','tp','tg','dm','do','ru','ec','er','fr','fo','pf','gf','tf','va','ph','fj','fi','cv','fk','gm','cg','cd','co','cr','gg','gd','gl','ge','cu','gp','gu','gy','kz','ht','kr','nl','an','hm','hn','ki','dj','kg','gn','gw','ca','gh','ga','kh','cz','zw','cm','qa','ky','km','ci','kw','cc','hr','ke','ck','lv','ls','la','lb','lt','lr','ly','li','re','lu','rw','ro','mg','im','mv','mt','mw','my','ml','mk','mh','mq','yt','mu','mr','us','um','as','vi','mn','ms','bd','pe','fm','mm','md','ma','mc','mz','mx','nr','np','ni','ne','ng','nu','no','nf','na','za','aq','gs','eu','pw','pn','pt','jp','se','ch','sv','ws','yu','sl','sn','cy','sc','sa','cx','st','sh','kn','lc','sm','pm','vc','lk','sk','si','sj','sz','sd','sr','sb','so','tj','tw','th','tz','to','tc','tt','tn','tv','tr','tm','tk','wf','vu','gt','ve','bn','ug','ua','uy','uz','es','eh','gr','hk','sg','nc','nz','hu','sy','jm','am','ac','ye','iq','ir','il','it','in','id','uk','vg','io','jo','vn','zm','je','td','gi','cl','cf','cn','yr','com','arpa','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','me','mobi','asia','ax','bl','bq','cat','cw','gb','jobs','mf','rs','su','sx','tel','travel'
        );

        if(!preg_match("/^http/is", $url)){
            $url="http://".$url;
        }

        $res = array();
        // $res->domain = null;
        // $res->host = null;
        $url_parse = parse_url(strtolower($url));
        $urlarr = explode(".", $url_parse['host']);
        $count = count($urlarr);
        // echo $url;
        if($count <= 2){
            #当域名直接根形式不存在host部分直接输出
            $res['domain'] = $url_parse['host'];
        }elseif($count > 2){
            $last = array_pop($urlarr);
            $last_1 = array_pop($urlarr);
            $last_2 = array_pop($urlarr);

            $res['domain'] = $last_1.'.'.$last;
            $res['host'] = $last_2;

            if(in_array($last, $state_domain)){
                $res['domain']=$last_1.'.'.$last;
                $res['host']=implode('.', $urlarr);
            }

            if(in_array($last_1, $state_domain)){
                $res['domain'] = $last_2.'.'.$last_1.'.'.$last;
                $res['host'] = implode('.', $urlarr);
            }
            #print_r(get_defined_vars());die;
        }
        return $res['domain'];
	} 
	

	/**
	 * [init_nginx 生成ngxin 配置文件]
	 * @Nomius
	 * @DateTime 2018-05-03T18:14:05+0800
	 * @param    [type]                   $domain [description]
	 * @return   [type]                           [description]
	 */
	function init_nginx($data){
		
		$filename = "{$data['table_prefix']}.conf";
		$content = str_replace('{{domain}}', $data['domain'],file_get_contents('nginx.conf'));
		file_put_contents($filename,$content);
	}

	/**
	 * [init_sql 生成sql文件]
	 * @Nomius
	 * @DateTime 2018-05-03T18:14:26+0800
	 * @param    [type]                   $domain [description]
	 * @param    [type]                   $title  [description]
	 * @return   [type]                           [description]
	 */
	function init_sql($data){
	
		$domain = str_replace(array('http://','https://'),'', $data['domain']);
		$domain = 'http://'.$domain;
		$str = file_get_contents('demo.sql');
		$content = str_replace(array('{{table_prefix}}','{{domain_name}}','{{url}}'), array($data['table_prefix'],$data['title'],$domain), $str);
		file_put_contents('now.sql', $content);

	}

	/**
	 * [init_config 生成配置文件]
	 * @Nomius
	 * @DateTime 2018-05-03T18:15:13+0800
	 * @param    [type]                   $domain [description]
	 * @param    [type]                   $title  [description]
	 * @return   [type]                           [description]
	 */
	function init_config($configs){
		$content = '<?php'."\n";
		$content.='return '.var_export($configs,TRUE).";\n";
		$content.= '?>';
		file_put_contents('config.php',$content);
	}

	/**
	 * [init_bash 生成bash文件]
	 * @Nomius
	 * @DateTime 2018-05-03T18:17:14+0800
	 * @return   [type]                   [description]
	 */
	function init_bash($data){
		//挪动nginx配置文件
		$content = "/usr/bin/mysql wp < /www/zhanqun/now.sql"."\n";
		$content.= "mv /www/zhanqun/{$data['table_prefix']}.conf /usr/local/nginx/conf/vhost/{$data['table_prefix']}.conf  \n";
		$content.= " /usr/local/nginx/sbin/nginx -s reload \n";
		file_put_contents('zhandian.sh',$content);
	}

	function error($msg){
		echo "<script>alert('$msg');window.location.href='/index.php';</script>";
		exit;
	}

	$posts = $_POST;
	$script = $a = '';
	if($posts){
		$title = trim($posts['title']);
		$domain = trim($posts['domain']);
		$base_domain = getBaseDomain($domain);
		$configs = include 'config.php';
		if(isset($configs[$base_domain])){
			error('域名已经存在!');
		}
		$table_prefix = str_replace('.','',$base_domain).'_';
		$configs[$base_domain] = array('table_prefix'=>$table_prefix,'title'=>$title,'domain'=>$domain) ;
		init_config($configs); //生成配置文件
		init_sql($configs[$base_domain]); //生成sql文件
		init_nginx($configs[$base_domain]); //生成nginx 配置文件
		init_bash($configs[$base_domain]); //生成脚本文件
		$script = "alert('添加成功,请等待一分钟后台再操作!')";
		$a = '<a href="http://'.$domain.'/wp-admin/" target="_blank">去访问站点(用户名:admin 密码123qwert)</a>';
	}
	


?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>站群管理</title>
    <link rel="shortcut icon" href="favicon.ico"> <link href="css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="css/style.css?v=4.1.0" rel="stylesheet">
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen  animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name">站</h1>
            </div>
            <h3>一分钟内只能添加一个</h3>

            <form class="m-t" role="form" action="/index.php" method="POST">
                <div class="form-group">
                    <input type="text" name="domain" class="form-control" placeholder="域名,别带http://" required="">
                </div>
                <div class="form-group">
                    <input type="text" name="title" class="form-control" placeholder="站点名称" required="">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">创 建</button>
		<?php echo $a;?>  
          </form>
        </div>
    </div>
	<script type="text/javascript">
    	<?php echo $script;?>
    </script>
</body>
</html>
