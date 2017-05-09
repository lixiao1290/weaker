<?php
namespace minicore\helper;

/**
 *
 * @author lixiao
 *         数据库操作类
 */
class Db extends Helper
{

    private $sqlLast;

    private $pdo;

    public $db;

    private $table;

    private $dsn;

    private $config;

    private $statement;

    private $fields;

    private $selectSql;

    private $where;

    /* array( array('字段','=','值') ,) */
    private $pars;

    private $wherepars;

    public $fetchstyle = \PDO::FETCH_ASSOC;

    public $host;

    public $user;

    public $pwd;

    public $self;

    private $type = 'mysql';

    public $sql;

    /**
     *
     * @return the $wherepars
     */
    public function getWherepars()
    {
        return $this->wherepars;
    }

    /**
     *
     * @param field_type $wherepars            
     */
    public function setWherepars($wherepars)
    {
        $this->wherepars = $wherepars;
    }

    /**
     *
     * @return the $sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     *
     * @param string $sql            
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }

    public $executePars;

    /**
     *
     * @return the $executePars
     */
    public function getExecutePars()
    {
        return $this->executePars;
    }

    /**
     *
     * @param field_type $executePars            
     */
    public function setExecutePars($executePars)
    {
        $this->executePars = $executePars;
    }

    /**
     *
     * @return the $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     *
     * @param
     *            Ambigous <string, unknown> $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     *
     * @return the $pdo
     */
    public function getPdo()
    {
        if (is_object($this->pdo)) {
            return $this->pdo;
        } else {
            return $this->pdoInit();
        }
    }

    /**
     *
     * @param \PDO $pdo            
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    public static function database($db)
    {
        $static = new static();
        if (is_array($db)) {
            $static->miniObjInit($db);
        } else {
            $static->miniObjInit();
            /*
             * if (Mini::$app->getConfig('db')) {
             * $static->miniObjInit(Mini::$app->getConfig('db'));
             * }
             */
        }
        $static->db = $db;
        return $static;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function field($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function debug()
    {
        var_dump($this->db, $this->fields, $this->table, $this->pdo->errorInfo(), $this->statement->errorInfo());
    }

    public function asObj()
    {
        $this->fetchstyle = \PDO::FETCH_OBJ;
        return $this;
    }

    public function asClass()
    {
        $this->fetchstyle = \PDO::FETCH_CLASS;
        return $this;
    }

    public function asArray()
    {
        $this->fetchstyle = \PDO::FETCH_ASSOC;
        return $this;
    }

    private function pdoInit()
    {
        try {
            if (is_object($this->pdo)) {
                return $this->pdo;
            } else {
                $this->dsn = $this->type . ':' . 'dbname=' . $this->db . ';host=' . $this->host;
                $this->setPdo((new \PDO($this->dsn, $this->user, $this->pwd)));
                return $this->pdo;
            }
        } catch (\PDOException $e) {
            echo $e->errorInfo;
        }
    }

    private function creatParameters($array)
    {
        $rs = [];
        foreach ($array as $key => $value) {
            $rs[':' . $key] = $value;
        }
        
        return $rs;
    }

    public function insert($data)
    {
        try {
            $pars = $this->creatParameters($data);
            // var_dump($pars);
            $fields = array_keys($data);
            if (empty($this->table)) {
                throw new \Exception('在添加数据时，未指定表名！');
            }
            if (empty($data)) {
                throw new \Exception('在添加数据时，未给定数据格式化的数组！');
            }
            $sql = <<<SQL
                INSERT INTO $this->table(implode(',', array_keys($data))values(implode(',', array_keys($pars))
SQL;
            
            $pdo = self::pdoInit();
            $statement = $pdo->prepare($sql);
            $statement->execute($pars);
            return $pdo->lastInsertId();
            if (\PDO::ERR_NONE != $statement->errorCode()) {
                return $this->pdo->lastInsertId();
            } else {
                var_dump($statement->debugDumpParams());
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function delete()
    {
        if (empty($this->where) || empty($this->wherepars)) {
            echo '未给出条件';
        }
        if (empty($this->table)) {
            echo '未给出表';
        } else {
            $sql = <<<SQL
            delete from $this->table where $this->where 
SQL;
            echo $sql;
        }
    }

    public function SqlizePars($pars)
    {
        $str = <<<SQL
set
SQL;
        $keys = array_keys($pars);
        $end = count($keys) - 1;
        foreach ($keys as $k => $vo) {
            if ($k == $end) {
                $str .= <<<SQL
 $vo = :$vo
SQL;
            } else {
                
                $str .= <<<SQL
 $vo = :$vo,
SQL;
            }
        }
        return $str;
    }

    public function update($pars)
    {
        if (empty($this->where) || empty($this->wherepars)) {
            echo '未给出条件';
        }
        if (empty($this->table)) {
            echo '未给出表';
        } else {
            $parsStr = $this->SqlizePars($pars);
            $this->setSql(<<<SQL
            update $this->table $parsStr where $this->where
SQL
);
            $pars = array_merge($this->creatParameters($pars), $this->getWherepars());
            echo $this->getSql();
            var_dump($pars);
            $this->execute($this->getSql(), $pars);
            $this->debug();
        }
    }

    public function select($fields = '*')
    {
      
        try {
            $fieldStr = ' ';
            if (! empty($this->fields)) {
                if (is_array($this->fields)) {
                    
                    foreach ($this->fields as $k => $v) {
                        $fieldStr .= "{$k} as {$v} "; // key 字段 value as
                    }
                } else {
                    $fieldStr = $this->fields;
                }
            } else {
                
                if (is_array($fields)) {
                    foreach ($fields as $k => $v) {
                        $fieldStr .= "{$k} as {$v} "; // key 字段 value as
                    }
                } else {
                    $fieldStr = $fields;
                }
            }
            $this->setSql(<<<SQL
            SELECT   $fieldStr   from   $this->table
SQL
);
            // echo $this->selectSql;
            if (! empty($this->wherepars)) {
                $this->selectSql .= ' where ' . $this->where;
            }
            $this->exec($this->getSql(), $this->getWherepars());
            return $this->statement->fetchAll($this->fetchstyle);
        } catch (\PDOException $e) {
            echo $e->errorInfo;
        }
    }

    public function where(array $where)
    {
        try {
            if (empty($where[0])) {
                throw new \Exception('where条件字段未给出');
            }
            if (empty($where[1])) {
                throw new \Exception('where条件类型未给出');
            }
            if (empty($where[2])) {
                throw new \Exception('where条件字段的值未给出');
            } else {
                
                $this->where .= ' ' . $where[0] . ' ' . $where[1] . ' :' . $where[0];
                $this->wherepars[':' . $where[0]] = $where[2];
            }
            return $this;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit();
        }
        return $this;
    }

    public function filteWhere(array $where)
    {
        if (! empty($where)) {
            try {
                if (empty($where[0])) {
                    throw new \Exception('where条件字段未给出');
                }
                if (empty($where[1])) {
                    throw new \Exception('where条件类型未给出');
                }
                if (empty($where[2])) {
                    throw new \Exception('where条件字段的值未给出');
                } else {
                    
                    $this->where .= ' ' . $where[0] . ' ' . $where[1] . ' :' . $where[0];
                    $this->wherepars[':' . $where[0]] = $where[2];
                }
                return $this;
            } catch (Exception $e) {
                echo $e->getMessage();
                exit();
            } catch (\PDOException $e) {
                echo $e->getMessage();
                exit();
            }
        }
        
        return $this;
    }

    public function exec($sql, $pars = NULL)
    {
        self::pdoInit();
        $this->statement = $this->pdo->prepare($sql);
        $this->statement->execute($pars);
    }

    /**
     * 执行数据库查询
     *
     * @return boolean
     */
    public function execute($sql, $pars)
    {
        try {
            $pdo = $this->pdoInit();
            $this->statement = $pdo->prepare($this->sql);
            return $this->statement->execute($pars);
        } catch (Exception $e) {
            echo $e->getMessage();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

