<?php

namespace PSF;

use MySQLiLib\MySQLDb;

class Model
{
    protected $db = null;
    static $_db = array();
    protected $dbName = '';
    protected $callerClassName = '';

    public function __construct( $dbName = '', $user = Constant::NONE, $password = Constant::NONE, $host = Constant::NONE, $port = Constant::MYSQL_PORT )
    {
        $backtrace = debug_backtrace();
        $callerClassName = '';
        foreach($backtrace as $fileInfo){
            $callerClassName = get_class($fileInfo['object']) ."|". $fileInfo['function']."|". $fileInfo['line'] ;
            break;
        }
        $this->callerClassName = $callerClassName;
        $callerClassName = sha1($this->callerClassName);

        if (empty(self::$_db[$callerClassName]) == true) {
            if(empty($user)||empty($password)||empty($host)){
                $options = Config::db( $dbName );
                if(!$dbName) {
                    $dbName = @array_shift(array_keys($options));
                    $options = array_shift($options);
                }
                $host = $options['host'];
                $user = $options['username'];
                $password = $options['password'];
                $dbName = $options['database'];
                $port = $options['port'];
            }
            $this->dbName =$dbName;

            self::$_db[$callerClassName] = new MySQLDb($host, $user, $password, $dbName, $port);
        }
        $this->db = static::$_db[$callerClassName];
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function disconnect()
    {
        return $this->db->close();
    }

    /**
     * @param $query
     * @param array $params
     * @return bool|mixed|\mysqli_result|null
     */
    public function query($query, $params = array())
    {
        return $this->db->query($query, $params);
    }

    /**
     * 요청쿼리에 맞는 데이터 여러행을 리턴
     * @param $query    데이터요청 쿼리
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = array())
    {
        return $this->db->fetchAll($query, $params);
    }

    /**
     * 요청 쿼리에 맞는 데이터 한행을 리턴
     * @param $query    데이터 요청 쿼리
     * @param array $params
     * @return array|bool
     */
    public function fetch($query, $params = array())
    {
        return $this->db->fetch($query, $params);
    }

    public function countRows($query, $params = array())
    {
        return mysqli_num_rows($this->query($query, $params));
    }

    /**
     * 마지막 입력 번호 가져오기 (auto increment column)
     * @return bool|int
     */
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * SQL Injection 방어 mysql_real_escape_string 실행
     * @param array $params
     * @return array
     */
    public function arrayToRealEscape( $params = array() )
    {
        return $this->db->arrayToRealEscape($params);
    }

    public function realEscapeString( $value )
    {
        return $this->db->realEscapeString($value);
    }

    /**
     * 숫자형 배열을 홑따옴표로 묶어준다
     * @param array $arrayVal
     * @return array
     */
    public function intArrayQuote( $arrayVal = array() )
    {
        return $this->db->intArrayQuote($arrayVal);
    }

    /**
     * 키:값 배열을 쿼리문에 넣기좋게 만들어준다
     * @param array $params
     * @return array
     */
    public function parseArrayToQuery( $params = array() )
    {
        return $this->db->parseArrayToQuery($params);
    }
}