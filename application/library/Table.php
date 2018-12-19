<?php

/**
 * Created by PhpStorm.
 * User: jaackie
 * Date: 2017/10/24
 * Time: 下午6:12
 */
class Table
{
    /**
     * 表名
     * @var string
     */
    public $table;

    /**
     * sql拼接
     * @var string
     */
    private $_sql;

    /**
     * 字段
     * @var string
     */
    private $_fields = '*';

    /**
     * 条件拼接
     * @var string
     */
    private $_where;

    /**
     * 修改时set的拼接
     * @var string
     */
    private $_set = '';

    /**
     * 分组拼接
     * @var string
     */
    private $_group;

    /**
     * 排序拼接
     * @var string
     */
    private $_order;

    /**
     * 限制拼接
     * @var string
     */
    private $_limit;

    /**
     * 参数组合
     * @var array
     */
    private $_params = [];


    /**
     * 遵循coreLogger中debug type,  null为关闭debug
     * @var null
     */
    private $_debug_type = null;

    /**
     * @var Table
     */
    private static $_instance = null;

    /**
     * @var Database $_dbInstance
     */
    private static $_dbInstance = null;

    /**
     * Table constructor.
     * @param $tableName
     */
    public function __construct($tableName = '')
    {
        $this->setTable($tableName);
    }

    /**
     * @param $tableName
     * @return Table
     */
    public static function instance($tableName = '')
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($tableName);
        }
        return self::$_instance;
    }

    /**
     * 设置表名
     * @param $tableName
     * @return $this
     */
    public function setTable($tableName)
    {
        $this->table = self::_transFields($tableName);
        return $this;
    }

    /**
     * 设置字段
     * @param string $fields 个别字段: a,b,c  全部: *
     * @return $this
     */
    public function fields($fields = '*')
    {
        $this->_fields = $fields;
        return $this;
    }

    /**
     * 条件拼接
     * @param string $where 正常sql条件语句
     * @param string $connect 与前个条件的逻辑关系(如果有的话),  'and', 'or'
     * @return $this
     */
    public function where($where, $connect = 'and')
    {
        if ($this->_where) {
            $this->_where .= $connect . ' ' . $where . ' ';
        } else {
            $this->_where = ' where ' . $where . ' ';
        }
        return $this;
    }

    /**
     * 适用于where id in (id1, id2, id3...)
     * @param string $field 字段名
     * @param array|string $inCon 值数组或是以','分隔的字符串
     * @param string $connect 逻辑连接
     * @return $this
     */
    public function whereIn($field, $inCon, $connect = 'and')
    {
        $i = 0;
        $in_arr = [];

        if (is_string($inCon)) {
            $inCon = explode(',', $inCon);
        }

        foreach ($inCon as $val) {
            $param_name = ":_wi_{$field}_{$i}";
            $in_arr[] = $param_name;
            $this->_params[$param_name] = $val;
            $i++;
        }
        $where = self::_transFields($field) . ' in (' . implode(',', $in_arr) . ')';
        $this->where($where, $connect);

        return $this;
    }

    /**
     * 单个字段操作的条件
     * (注意:当用到相同字段多次时得注意参数名称可能因为字段名相同而导致不同参数被相同的参数名称覆盖, 建议直接用where方法)
     * @param string $field 操作字段
     * @param mixed $value 字段值
     * @param string $expression 操作, '=',
     * @param string $connect 逻辑连接
     * @return $this
     */
    public function whereField($field, $value, $expression = '=', $connect = 'and')
    {
        $param_name = ":_wf_{$field}";
        $where = self::_transFields($field);

        $where .= " {$expression} {$param_name}";

        $this->where($where, $connect);
        $this->_params[$param_name] = $value;

        return $this;
    }

    /**
     * 单个字段between操作
     * @param $field
     * @param $min
     * @param $max
     * @param string $connect
     * @return $this
     */
    public function whereBetween($field, $min, $max, $connect = 'and')
    {
        $param_name_min = ":_wbl_{$field}";
        $param_name_max = ":_wbu_{$field}";
        $field = self::_transFields($field);

        $this->where("{$field} between {$param_name_min} and {$param_name_max}", $connect);

        $this->_params[$param_name_min] = $min;
        $this->_params[$param_name_max] = $max;

        return $this;
    }

    /**
     * 设置键值(update时)
     * @param string $field 待修改字段名
     * @param mixed $value 修改值
     * @param null|string $expression 操作如'+','-'
     * @return $this
     */
    public function set($field, $value, $expression = null)
    {
        if ($this->_set) $this->_set .= ', ';

        $trans_field = self::_transFields($field);
        $param_name = ":_s_{$field}";
        if ($expression) {
            $this->_set .= " {$trans_field} = $trans_field {$expression} {$param_name}";
        } else {
            $this->_set .= " {$trans_field} = {$param_name}";
        }
        $this->_params[$param_name] = $value;


        return $this;
    }

    /**
     * 分组
     * @param string $groupByField 待分组字段
     * @return $this
     */
    public function group($groupByField)
    {
        $this->_group = ' group by ' . self::_transFields($groupByField) . ' ';
        return $this;
    }

    /**
     * 排序
     * @param string $orderByField 待排序字段
     * @param bool $isIncrease 递增:true, 递减:false
     * @return $this
     */
    public function order($orderByField, $isIncrease = true)
    {
        if ($isIncrease) {
            $order = 'asc';
        } else {
            $order = 'desc';
        }

        $orderByField = self::_transFields($orderByField);
        if (!$this->_order) {
            $this->_order = ' order by ' . $orderByField . ' ' . $order . ' ';
        } else {
            $this->_order .= ', ' . $orderByField . ' ' . $order . ' ';
        }

        return $this;
    }

    /**
     * 分页(基于limit)
     * @param int $items 每页条数
     * @param int $page 页数
     * @param bool $more 是否需要多取以为, 用于计算下一页是否存在
     * @return $this
     */
    public function page($items, $page, $more = false)
    {
        $items = intval($items);
        $page = intval($page);
        $page = $page <= 0 ? 1 : $page;
        $start = ($page - 1) * $items;

        if ($more) $items++;    //如果需要多取1位，计算还有没有更多

        $this->limit($start, $items);
        return $this;
    }

    /**
     * 限制个数
     * @param int $start 起始
     * @param int $offset 偏移数量
     * @return $this
     */
    public function limit($start, $offset = null)
    {
        $start = intval($start);
        $offset = intval($offset);
        $offset = $offset ? ",{$offset}" : '';

        $this->_limit = " limit {$start}{$offset}";

        return $this;
    }

    /**
     * 设置参数
     * @param mixed $param 为数组时合并到params中, 为字符串时加入到params
     * @param $value
     * @return $this
     */
    public function param($param, $value = null)
    {
        if (is_array($param)) {
            $params = [];
            foreach ($param as $key => $value) {
                $params[':' . $key] = $value;
            }
            $this->_params = array_merge($this->_params, $params);
        } else {
            $this->_params[':' . $param] = $value;
        }

        return $this;
    }

    /**
     * 拼接sql句子
     */
    private function _makeSql()
    {
        if ($this->_where) {
            $this->_sql .= $this->_where;
        }
        if ($this->_group) {
            $this->_sql .= $this->_group;
        }
        if ($this->_order) {
            $this->_sql .= $this->_order;
        }
        if ($this->_limit) {
            $this->_sql .= $this->_limit;
        }
    }

    /**
     * 获取查询结果
     * @return array
     */
    public function get()
    {
        $this->_dealFields();
        $this->_sql = "select {$this->_fields} from {$this->table}";
        $this->_makeSql();
        $result = $this->query($this->_sql, $this->_params);
        return $result ? $result : [];
    }

    /**
     * 获取查询单条结果
     * @return array|bool
     */
    public function getOne()
    {
        $result = $this->get();
        return $result ? $result[0] : $result;
    }

    /**
     * 统计条数
     * @return bool|int
     */
    public function count()
    {
        $this->_sql = "select count(1) as `rows` from {$this->table}";
        $this->_makeSql();
        $result = $this->query($this->_sql, $this->_params);
        return $result ? intval($result[0]['rows']) : $result;
    }

    /**
     * 插入数据
     * @param array $values ['field_1'=>$value_1,'field_2'=>$value_2]
     * @param bool $lastId 是否返回最后插入id
     * @param null|string $onDuplicate 重复时更新字段字符串, 多个以','分隔, 支持'+','-'操作, 如: a,+b,-c,d
     * @return bool|int
     */
    public function insert($values = [], $lastId = false, $onDuplicate = null)
    {
        $this->_fields = implode(',', array_keys($values));

        $values_arr = [];
        foreach ($values as $field => $value) {
            $param_name = ":_insert_{$field}";
            $this->_params[$param_name] = $value;
            $values_arr[] = $param_name;
        }
        $values_str = implode(',', $values_arr);

        $this->_dealFields();
        $this->_sql = "insert into {$this->table} ({$this->_fields}) values ({$values_str})"
            . self::_onDuplicate($onDuplicate);
        $result = $this->execute($this->_sql, $this->_params);
        if ($lastId) {
            $result = self::dbInstance()->lastInsertId();
        }
        return $result;
    }

    /**
     * 当唯一索引重复时sql语句拼接
     * @param string $onDuplicateString 更新字段,多个以','分隔, 支持'+','-'操作, 如: a,+b,-c,d
     * @return string
     */
    private static function _onDuplicate($onDuplicateString)
    {
        if (!$onDuplicateString) return '';

        $update_arr = explode(',', $onDuplicateString);
        $str_arr = [];
        foreach ($update_arr as $field) {
            if (preg_match('/^([+-])(\w+)/s', $field, $res)) {
                $field = $res[2];
                $str_arr[] = "`{$field}` = `{$field}` {$res[1]} values(`{$field}`)";
            } else {
                $str_arr[] = "`{$field}` = values(`{$field}`)";
            }
        }
        return ' on duplicate key update ' . implode(',', $str_arr);
    }

    /**
     * 同时插入多个(插入字段为插入数组的第一个)
     * @param array $valuesArr 多个插入数组[['f1'=>$val1,'f2'=>$val2],['f1'=>$val3,'f2'=>$val4]...]
     * @param array $onDuplicate
     * @return bool|int
     */
    public function insertMulti($valuesArr, $onDuplicate = null)
    {
        if (!$valuesArr) return 0;

        $this->_fields = implode(',', array_keys($valuesArr[0]));
        $fields_arr = explode(',', $this->_fields);

        $values = [];
        $i = 0; //参数名计数
        foreach ($valuesArr as $val) {
            $fields_tmp = [];
            foreach ($fields_arr as $field) {
                $param_name = ":_insert_{$field}_{$i}";
                $fields_tmp[] = $param_name;
                $this->_params[$param_name] = $val[$field];
            }
            $insert_str = implode(',', $fields_tmp);
            $values[] = "({$insert_str})";
            $i++;
        }

        $this->_dealFields();
        $this->_sql = "insert into {$this->table} ({$this->_fields}) values ";
        $this->_sql .= implode(',', $values);
        $this->_sql .= self::_onDuplicate($onDuplicate);

        return $this->execute($this->_sql, $this->_params);
    }

    /**
     * 单条记录更新
     * @return int|bool
     */
    public function update()
    {
        $this->_sql = "update {$this->table} set {$this->_set}";
        $this->_makeSql();
        return $this->execute($this->_sql, $this->_params);
    }

    /**
     * 通过键值形式更新多个且不同值
     * @param string $field 更新字段
     * @param string $case 更新的索引字段
     * @param array $updateArr 更新的数组 [$case1 => $value1, $case2 => $value2 ...]
     * @param string $opt 更新的操作, '=', '+', '-'...
     * @return int|bool
     */
    public function updateMulti($field, $case, $updateArr, $opt = '=')
    {
        if (!$updateArr) return 0;

        $trans_field = self::_transFields($field);  //经过转义的字段
        $trans_case = self::_transFields($case);

        $this->_sql = "update {$this->table} set {$trans_field} = case {$trans_case}";

        $field_opt = $opt == '=' ? '' : $trans_field . $opt;

        $i = 0;
        foreach ($updateArr as $field_val => $val) {
            $param_name = ":_update_{$field}_{$i}";
            $case_name = ":_case_{$i}";
            $this->_sql .= " when {$case_name} then {$field_opt} {$param_name} ";
            $this->_params[$case_name] = $field_val;
            $this->_params[$param_name] = $val;

            $i++;
        }
        $this->whereIn($case, array_keys($updateArr));

        $this->_sql .= "else {$trans_field} end {$this->_where}";

        return $this->execute($this->_sql, $this->_params);
    }

    /**
     * 删除
     * @return int|bool
     */
    public function delete()
    {
        $this->_sql = "delete from {$this->table}";
        $this->_makeSql();
        return $this->execute($this->_sql, $this->_params);
    }

    /**
     * 设置debug
     * @param $debugType
     * @return $this
     */
    public function debug($debugType)
    {
        $this->_debug_type = $debugType;
        return $this;
    }

    /**
     * 是否要debug输出sql句子与参数
     */
    private function _sqlDebug()
    {
        if ($this->_debug_type === null) return;

        echo $this->_sql;
        var_export($this->_params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|bool
     */
    public function query($sql, $params = [])
    {
        $this->_sqlDebug();
        $res = self::dbInstance()->query($sql, $params);
        $this->_clear();
        return $res;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int|bool
     */
    public function execute($sql, $params = [])
    {
        $this->_sqlDebug();
        $res = self::dbInstance()->exec($sql, $params);
        $this->_clear();
        return $res;
    }

    /**
     * 处理字段名, 防止与mysql关键字冲突
     */
    private function _dealFields()
    {
        if ($this->_fields === '*') return;

        $this->_fields = self::_transFields($this->_fields);
    }

    /**
     * 转化字段, 防止与mysql关键字冲突
     * @param $fields
     * @return string
     */
    private static function _transFields($fields)
    {
        $field_arr = explode(',', $fields);
        foreach ($field_arr as &$field) {
            $field = "`$field`";
        }
        return implode(',', $field_arr);
    }

    /**
     * 当一个对象被重复多次用于数据库操作时，清除上一个sql语句残留的成员属性
     */
    private function _clear()
    {
        $this->_sql = '';
        $this->_fields = '*';
        $this->_where = '';
        $this->_set = '';
        $this->_group = '';
        $this->_order = '';
        $this->_limit = '';
        $this->_params = [];
    }

    /**
     * @Usage self::dbInstance()->query($sql, [$param1, $param2]);
     * @return Database
     */
    protected static function dbInstance()
    {
        if (!(self::$_dbInstance instanceof Database)) {
            $dbConfig = Yaf_Application::app()->getConfig()['database']['master'];
            $config = [
                'dns' => 'mysql:host=' . $dbConfig['server'] . ';dbname=' . $dbConfig['database'] . ';charset=utf8',
                'user' => $dbConfig['user'],
                'pass' => $dbConfig['password'],
            ];
            self::$_dbInstance = Database::getInstance($config);
        }
        return self::$_dbInstance;
    }
}
