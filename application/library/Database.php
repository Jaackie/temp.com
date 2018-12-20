<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 12/11/15
 * Time: 16:16
 */
class Database
{
    private $_pdo;
    private $_dns = '';
    private $_user = '';

    private function __construct($dns, $user, $pass)
    {
        $this->_dns = $dns;
        $this->_user = $user;
        $this->_pdo = new PDO($dns, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /**
     * @param array $config
     * @return Database
     */
    public static function getInstance(array $config)
    {
        try {
            return new self($config['dns'], $config['user'], $config['pass']);
        } catch (Exception $exp) {
            return self::_halt('Init [ dns=' . $config['dns'] . ' ]', $exp);
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|bool
     */
    public function query($sql, array $params = [])
    {
        try {
            $smt = $this->_pdo->prepare($sql);
            if ($smt->execute($params)) {
                return $smt->fetchAll();
            }
            return false;
        } catch (Exception $exp) {
            return self::_halt($sql, $exp);
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool|int
     */
    public function exec($sql, array $params = [])
    {
        try {
            $smt = $this->_pdo->prepare($sql);
            if ($smt->execute($params)) {
                return $smt->rowCount();
            }
            return false;
        } catch (Exception $exp) {
            return self::_halt($sql, $exp);
        }
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        try {
            return $this->_pdo->lastInsertId();
        } catch (Exception $exp) {
            return self::_halt('Get Last InsertId', $exp);
        }
    }

    /**
     * @param string $desc
     * @param Exception $exp
     * @return bool
     */
    private static function _halt($desc, Exception $exp)
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'null';
        $data = sprintf(
            "[%s]  %d\t%s\n%s\n%s\n--\n",
            date('Y-m-d H:i:s'),
            $exp->getCode(),
            $uri,
            $desc,
            $exp->getMessage()
        );
        $file = '/tmp/mysql.' . date('Ymd') . '.err';
        file_put_contents($file, $data, FILE_APPEND);
        return false;
    }
}