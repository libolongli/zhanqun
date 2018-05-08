<?php

    //封装一个DB类，用来专门操作数据库，以后凡是对数据库的操作，都由DB类的对象来实现
    class DB{
        //属性
        private $host;
        private $port;
        private $user;
        private $pass;
        private $dbname;
        private $charset;
        private $prefix;            //表前缀
        private $link;                //连接资源（连接数据库，一般会返回一个资源，所以需要定义一个link属性）

        //构造方法(作用：为了初始化对象的属性)，会被自动调用
        /*
         * @param1 array $arr，默认为空，里面是一个关联数组，里面有7个元素
         * array('host' => 'localhost','port' => '3306');
         */
        public function __construct($arr = array()){
            //初始化
            $this->host = isset($arr['host']) ? $arr['host'] : 'localhost';//先判断是否有自己的host，如果有就用自己的host，否则就使用默认的localhost
            $this->port = isset($arr['port']) ? $arr['port'] : '3306';
            $this->user = isset($arr['user']) ? $arr['user'] : 'root';
            $this->pass = isset($arr['pass']) ? $arr['pass'] : 'root';
            $this->dbname = isset($arr['dbname']) ? $arr['dbname'] : 'wp';
            $this->charset = isset($arr['charset']) ? $arr['charset'] : 'utf8';
            $this->prefix = isset($arr['prefix']) ? $arr['prefix'] : 'wp';

            //连接数据库（类是要操作数据库，因此要连接数据库）
            $this->connect();

            //设置字符集
            $this->setCharset();

            //选择数据库
            $this->setDbname();
        }

        /*
         * 连接数据库
        */
        private function connect(){
            //mysql扩展连接
            $this->link = mysql_connect($this->host . ':' . $this->port,$this->user,$this->pass);

            //判断结果
            if(!$this->link){
                //结果出错了
                //暴力处理，如果是真实线上项目（生产环境）必须写入到日志文件
                echo '数据库连接错误：<br/>';
                echo '错误编号' . mysql_errno() . '<br/>';
                echo '错误内容' . mysql_error() . '<br/>';
                exit;
            }
        }

        /*
         * 设置字符集
        */
        private function setCharset(){
            //设置
            $this->db_query("set names {$this->charset}");
        }

        /*
         * 选择数据库
        */
        private function setDbname(){
            $this->db_query("use {$this->dbname}");
        }

        /*
         * 增加数据
         * @param1 string $sql，要执行的插入语句
         * @return boolean，成功返回是自动增长的ID，失败返回FALSE
        */
        public function db_insert($sql){
            //发送数据
            $this->db_query($sql);
            
            //成功返回自增ID
            return mysql_affected_rows() ? mysql_insert_id() : FALSE;
        }

        /*
         * 删除数据
         * @param1 string $sql，要执行的删除语句
         * @return Boolean，成功返回受影响的行数，失败返回FALSE
        */
        public function db_delete($sql){
            //发送SQL
            $this->db_query($sql);

            //判断结果
            return mysql_affected_rows() ? mysql_affected_rows() : FALSE;
        }

        /*
         * 更新数据
         * @param1 string $sql，要执行的更新语句
         * @return Boolean，成功返回受影响的行数，失败返回FALSE
        */
        public function db_update($sql){
            //发送SQL
            $this->db_query($sql);

            //判断结果
            return mysql_affected_rows() ? mysql_affected_rows() : FALSE;
        }

        /*
         * 查询：查询一条记录
         * @param1 string $sql，要查询的SQL语句
         * @return mixed，成功返回一个数组，失败返回FALSE
        */
        public function db_getRow($sql){
            //发送SQL
            $res = $this->db_query($sql);

            //判断返回
            return mysql_num_rows($res) ? mysql_fetch_assoc($res) : FALSE;
        }

        /*
         * 查询：查询多条记录
         * @param1 string $sql，要查询的SQL语句
         * @return mixed，成功返回一个二维数组，失败返回FALSE
        */
        public function db_getAll($sql){
            //发送SQL
            $res = $this->db_query($sql);

            //判断返回
            if(mysql_num_rows($res)){
                //循环遍历
                $list = array();
                
                //遍历
                while($row = mysql_fetch_assoc($res)){
                    $list[] = $row;
                }

                //返回
                return $list;
            }

            //返回FALSE
            return FALSE;
        }

        /*
         * mysql_query错误处理
         * @param1 string $sql，需要执行的SQL语句
         * @return mixed，只要语句不出错，全部返回
        */
        public function db_query($sql){
            //发送SQL
            $res = mysql_query($sql);

            //判断结果
            if(!$res){
                //结果出错了
                //暴力处理，如果是真实线上项目（生产环境）必须写入到日志文件
                echo '语句出现错误：<br/>';
                echo '错误编号' . mysql_errno() . '<br/>';
                echo '错误内容' . mysql_error() . '<br/>';
                exit;
            }
            //没有错误
            return $res;
        }
            //__sleep方法
        public function __sleep(){
            //返回需要保存的属性的数组
            return array('host','port','user','pass','dbname','charset','prefix');
        }

        //__wakeup方法
        public function __wakeup(){
            //连接资源
            $this->connect();
            //设置字符集和选中数据库
            $this->setCharset();
            $this->setDbname();
        }    

        /*
         * 获取完整的表名
        */
        protected function getTableName(){
            //完整表名：前缀+表名
            return $this->prefix . $this->table;
        }
    }
//这个DB类，一般不写析构（不释放资源） 

?>