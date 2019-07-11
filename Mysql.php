<?php
class MysqlException extends  RuntimeException {}

class Mysql
{
    protected static $dao;

    protected $dbh;

    // 禁止通过new生成对象
    private function __construct($host, $dbname, $user, $pass) {
        //此处连接要抓取数据连接产生的异常，防止泄露掉数据库的用户和密码信息
        try {
            $this->dbh = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $user, $pass);
        } catch (PDOException $e) {
            $this->outputError('数据库连接失败');
        }

    }

    // 禁止通过clone复制对象
    private function __clone() {}

    // 禁止重建对象
    private function __wakeup(){}

    /**
     *  获取连接mysql类对象
     *
     * @param $host string ip主机
     * @param $dbname string
     * @param $user
     * @param $pass
     * @return MysqlDao
     */
    public static function getInstance($host, $dbname, $user, $pass) :  Mysql
    {
        if (!isset(self::$dao)) {
            self::$dao = new self($host, $dbname, $user, $pass);
        }
        return self::$dao;
    }

    /**
     * 抛出错误信息
     *
     * @param $errmsg
     */
    private function outputError($errmsg)
    {
        throw new MysqlException('Mysql Error: '. $errmsg);
    }

    /**
     * 开启事务
     *
     * @return bool
     */
    public function beginTransaction() : bool
    {
        // 对应mysql命令：begin 或者 start transaction
        return $this->dbh->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit() : bool
    {
        // 对应mysql命令：commit
        return $this->dbh->commit();
    }

    /**
     * 回滚
     *
     * @return bool
     */
    public function rollBack() : bool
    {
        // 对应mysql命令：rollback
        return $this->dbh->rollBack();
    }

    /**
     * 插入数据
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insert(string $table, array $data) : int
    {
        $rawSql = "insert into `$table` (`" . implode('`,`', array_keys($data)) . "`) values('" . implode("','", $data) . "')";
        $result = $this->dbh->exec($rawSql);
        if ($result === false) {
            $this->outputError($this->dbh->errorInfo()[2]);
        }
        return $result;
    }

    /**
     * 删除数据
     *
     * @param string $table
     * @param string $where
     * @return int
     */
    public function delete(string $table, string $where = '') : int
    {
        $rawSql = "delete from `$table`";
        if ($where) {
            $rawSql .= " where $where";
        }
        $result = $this->dbh->exec($rawSql);
        if ($result === false) {
            $this->outputError($this->dbh->errorInfo()[2]);
        }
        return $result;
    }

    /**
     * 更新数据
     *
     * @param string $table
     * @param array $data
     * @param string $where
     * @return int
     */
    public function update(string $table, array $data, string $where = '') : bool
    {
        if (!$data) {
            $this->outputError('更新数据不能为空');
        }
        array_walk($data, function (&$value, $key) {
            $value = "`$key` = '$value'";
        });
        $rawSql = "update `$table` set " . implode(',', $data);
        if ($where) {
            $rawSql .= " where $where";
        }
        $result = $this->dbh->exec($rawSql);
        if ($result === false) {
            $this->outputError($this->dbh->errorInfo()[2]);
        }
        return $result;
    }

    public function select()
    {

    }
}


