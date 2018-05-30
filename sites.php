<?php
	
	ini_set('display_errors','Off');
	/**
	 * 获取site列表 
	 */
	include 'db.php';
	$db = new DB();

	function getBaseDomain($url=''){
        if(!$url){
            return $url;
        }

        $url = trim($url);
        return str_replace(array('http://','https://'), '', $url);
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
	 * [get_data 获取数据]
	 * @Nomius
	 * @DateTime 2018-05-08T14:36:12+0800
	 * @param    [type]                   $db [description]
	 * @return   [type]                       [description]
	 */
	function get_data($db){
		$data = array();
		$ar = $db->db_getAll('SELECT * FROM wp_sites');
		if(!$ar) $ar = array();
		foreach ($ar as  &$value) {
			$value['op'] = "<a href='http://{$value['domain']}/wp-admin/' target='_blank'>后台登陆</a>&nbsp;&nbsp;&nbsp;<a href='javascript:;' onclick='delDomain({$value['id']})'>删除</a>";
		}
		$data['total'] = 1;
		$data['records'] = count($ar);
		$data['rows'] = $ar;
		exit(json_encode($data));
	}

	/**
	 * [del_data 删除]
	 * @Nomius
	 * @DateTime 2018-05-08T14:36:24+0800
	 * @param    [type]                   $db [description]
	 * @return   [type]                       [description]
	 */
	function del_data($db){
		$data = $_POST;
		$ids = explode(',',$data['id']);
		
		if($data['password'] != 'woaizhongguo'){
			echo json_encode(array('status'=>true,'msg'=>'密码错误'));
			exit;
		}
		foreach ($ids as  $id) {
			 del_single($db,$id);
		}


		echo json_encode(array('status'=>true,'msg'=>'删除成功'));
		exit;
	}

	function del_single($db,$id){
		$row = $db->db_getRow("SELECT * FROM wp_sites WHERE id='$id'");
		//删除对应的表
		$prefix = str_replace('_', '\_', $row['prefix']);
		$sql = "SHOW TABLES LIKE '{$prefix}%'";
		$data = $db->db_getAll($sql);
		$tables = array();
		foreach ($data as $key => $value) {
			$tables[] = array_pop($value);
		}

		foreach ($tables as  $table) {
			$sql = "DROP TABLE  {$table}";
			$db->db_query($sql);
		}

		$sql = "DELETE FROM  wp_sites WHERE id = {$row['id']}";
		$id = $db->db_delete($sql);
	}


	/**
	 * [add_data 添加数据]
	 * @Nomius
	 * @DateTime 2018-05-08T14:36:55+0800
	 * @param    [type]                   $db [description]
	 */
	function add_data($db){
		$data = $_POST;
		$insert_data = array();
		$insert_data['domain'] = getBaseDomain($data['domain']);
		if($db->db_getRow("SELECT * FROM wp_sites WHERE domain='{$insert_data['domain']}'")){
			exit(json_encode(array('status'=>false,'msg'=>'已经存在该域名')));
		}



		$insert_data['title'] = $data['title'];
		$insert_data['template'] = $data['template'];
		
		$r = $db->db_getRow("SELECT * FROM wp_sites  ORDER BY id DESC");
		$r['id'] = $r['id']++;
		$insert_data['prefix'] = 'wp'.$r['id'].'_';
		// $insert_data['prefix'] =  str_replace('.','',$insert_data['domain']).'_';
		
		if(!($insert_data['title']&&$insert_data['template']&&$insert_data['prefix'])){
			exit(json_encode(array('status'=>false,'msg'=>'参数为空!')));
		}
		$keys = '`'.join('`,`',array_keys($insert_data)).'`';
		$values = "'".join("','",$insert_data)."'";
		$sql = "INSERT INTO wp_sites ({$keys}) VALUE($values);";
		$id = $db->db_insert($sql);

		//初始化表述据
		init_data($db,$data['template'],$insert_data['prefix'],$insert_data['domain'],$insert_data['title']);

		//初始化nginx 配置文件
		init_nginx($db);

		init_bash();
		exit(json_encode(array('status'=>true,'msg'=>'添加成功')));
	}

	/**
	 * [init_data 初始化数据]
	 * @Nomius
	 * @DateTime 2018-05-08T15:32:16+0800
	 * @param    [type]                   $db     [description]
	 * @param    [type]                   $source [description]
	 * @param    [type]                   $target [description]
	 * @return   [type]                           [description]
	 */
	function init_data($db,$source,$target,$domain,$title){
		if($source == 'default') $source = 'wp';
		$sql = "SHOW TABLES LIKE '{$source}\_%'";
		$data = $db->db_getAll($sql);
		$tables = array();
		foreach ($data as $key => $value) {
			$tables[] = array_pop($value);
		}

		foreach ($tables as  $table) {
			if($table =='wp_sites') continue;
			$otable = str_replace("{$source}_",'',$table);
			$sql1 = "CREATE TABLE {$target}{$otable} LIKE $table";
			$db->db_query($sql1);
			$sql2 = "INSERT INTO {$target}{$otable} SELECT * FROM $table";
			$db->db_query($sql2);
		}

		$sql = "UPDATE {$target}options SET option_value = 'http://{$domain}' WHERE option_id = 1";
		$db->db_query($sql);
		$sql = "UPDATE {$target}options SET option_value = 'http://{$domain}' WHERE option_id = 2";
		$db->db_query($sql);
		$sql = "UPDATE {$target}options SET option_value = '{$title}' WHERE option_id = 3";
		$db->db_query($sql);

		$sql = "UPDATE {$target}options SET option_name = '{$target}user_roles' WHERE  option_id = 92";
		$db->db_query($sql);
		$sql = "UPDATE {$target}usermeta SET meta_key = '{$target}capabilities' WHERE umeta_id = 12";
		$db->db_query($sql);
		$sql = "UPDATE {$target}usermeta SET meta_key = '{$target}user_level' WHERE umeta_id = 13";
		$db->db_query($sql);

		//UPDATE 7baiducom_options SET option_name = '7baiducom_user_roles' WHERE  option_id = 92;
		//UPDATE 7baiducom_usermeta SET meta_key = '7baiducom_capabilities' WHERE umeta_id = 12;
		//UPDATE 7baiducom_usermeta SET meta_key = '7baiducom_user_level' WHERE umeta_id = 13;
	}

	/**
	 * [init_nginx 初始化nginx文件]
	 * @Nomius
	 * @DateTime 2018-05-08T15:46:21+0800
	 * @return   [type]                   [description]
	 */
	function init_nginx($db){
		$ar = $db->db_getAll('SELECT * FROM wp_sites');
		foreach ($ar as  $value) {
			$filename = "nginx/{$value['prefix']}.conf";
			//如果是WWW.的域名 nginx 同时写上 根域名的配置
			if(strpos($value['domain'],'www.')===0){
				$value['domain'] = str_replace('www.', '', $value['domain']);
				$content = str_replace('{{domain}}', $value['domain'],file_get_contents('nginx_root.conf'));
			}else{
				$content = str_replace('{{domain}}', $value['domain'],file_get_contents('nginx.conf'));
			}
			file_put_contents($filename,$content);
		}
	}

	/**
	 * [init_bash 生成bash文件]
	 * @Nomius
	 * @DateTime 2018-05-03T18:17:14+0800
	 * @return   [type]                   [description]
	 */
	function init_bash(){
		//挪动nginx配置文件
		$content= " /usr/local/nginx/sbin/nginx -s reload \n";
		file_put_contents('zhandian.sh',$content);
	}




	$type = $_GET['type'];

	if($type == 'get'){
		get_data($db);
	}elseif($type='eidt'){
		if($_POST['oper']=='del'){
			del_data($db);
		}elseif($_POST['oper']=='add'){
			add_data($db);
		}
	}


?>