<?php

namespace PSF;

use MySQLiLib\MySQLDb;

class Model
{
    protected $db = null;
    static $_db = array();
    protected $dbName = '';
    protected $callerClassName = '';

    public function __construct( $dbAlias = '', $user = Constant::NONE, $password = Constant::NONE, $host = Constant::NONE, $port = Constant::MYSQL_PORT )
    {
        if(!$dbAlias){
            $options = Config::db( $dbAlias );
            $dbAlias = @array_shift(array_keys($options));
            $options = array_shift($options);
        }
        if (empty(self::$_db[$dbAlias]) == true) {
            if(empty($user)||empty($password)||empty($host)){
                if(empty($options)){
                    $options = Config::db( $dbAlias );
                }
                $host = $options['host'];
                $user = $options['username'];
                $password = $options['password'];
                $dbName = $options['database'];
                $port = $options['port'];
            }
            $this->dbName =$dbName;
            self::$_db[$dbAlias] = new MySQLDb($host, $user, $password, $dbName, $port);
        }
        $this->db = static::$_db[$dbAlias];
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
     * 쿼리 반복 실행시 다음 행을 리턴
     * @param $query    데이터 요청 쿼리
     * @param array $params
     * @return array|bool
     */
    public function fetch($query, $params = array())
    {
        return $this->db->fetch($query, $params);
    }

    /**
     * 쿼리 반복실행해도 최초 한 행을 리턴
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function fetchOne($query, $params = array())
    {
        return $this->db->fetchOne($query, $params);
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