<?php

namespace App\Db;

use App\Core;

class Db
{

    public static $instance;
    public $error = false;
    public $console = false;
    public $connected = false;
    public $generatedSql = "";
    private $_pdo;
    private $_results = [];
    private $_count;
    private $_last_insert_id;
    private $_debug = false;
    private $_table = null;


    public function __construct($dbConfig, $consoleApp = false)
    {
        $this->console = $consoleApp;
        try {
            $this->_pdo = new \PDO(
                $dbConfig['dsn'],
                $dbConfig['username'],
                $dbConfig['password']
            );
            if (isset($dbConfig['debug']))
                $this->_debug = $dbConfig['debug'];
            $this->connected = true;
        } catch (\PDOException $PDOException) {
            $error = 'Cannot connect to the database<br>' . "\n";
            if ($this->_debug) {
                $error .= 'Error Message: ' . $PDOException->getMessage();
            }
            $this->error = $error;
            return false;
        }
    }

    public static function instantiate($dbConfig)
    {
        if (!isset(self::$instance)) {
            self::$instance = new Db($dbConfig);
        }
        return self::$instance;
    }

    public function t($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * Updates a one or more rows with $data by using $condition
     *
     * @param $data with columnName => value pairs
     * @param $condition Accepts an array with columnName => value pairs or a pure string to condition by it.
     * @return int number of affected rows
     */

    public function update($data, $condition)
    {
        if (!empty($data)) {
            $sql = "UPDATE `" . $this->_table . "` SET ";
            $params = [];
            foreach ($data as $column => $value) {
                $sql .= "`" . $column . "`=:d_" . $column . ", ";
                $params['d_' . $column] = $value;
            }
            $sql = substr($sql, 0, -2);
            if (!empty($condition)) {
                $conditionString = '';
                if (is_array($condition)) {
                    $conditions = [];
                    foreach ($condition as $column => $value) {
                        $conditions[] = "`" . $column . "`=:" . $column;
                        $params[$column] = $value;
                    }
                    $conditionString .= '(' . implode(" AND ", $conditions) . ')';
                } else {
                    $conditionString = $condition;
                }
                $sql .= ' WHERE ' . $conditionString;
            } else {
                $error = 'For security reasons you cannot update all the rows in a table without properly specifying a condition.' .
                    'You can use $db->update($data,"1=1") if you are certain you want to update all records.';
                $this->error = $error;
                return false;
            }
            $this->query($sql, $params);
            return $this->_count;

        } else return 0;
    }

    public function query($sql, $params = [], $returnArray = true)
    {
        $sql = str_replace(['[TABLE]'], ['`' . $this->_table . '`'], $sql);
        $this->generatedSql = $sql;
        $pdoStatement = $this->_pdo->prepare($sql);
        if ($pdoStatement->execute($params)) {
            $this->_results = [];
            while ($row = ($returnArray ? $pdoStatement->fetch(\PDO::FETCH_ASSOC) : $pdoStatement->fetchObject())) {
                $this->_results[] = $row;
            }
            $this->_count = $pdoStatement->rowCount();
            $this->_last_insert_id = $this->_pdo->lastInsertId();
        } else {
            $error = 'There was an error running your query<br>' . "\n";
            if ($this->_debug) {
                if (!$this->_table) {
                    $error = 'Please use $db->t() to set your working table before running queries<br>' . "\n";
                    $this->error = $error;
                    if (!$this->console)
                        Core::errorOut(500, $error);
                    return false;
                }
                $errorInfo = $pdoStatement->errorInfo();
                $error .= 'SQL Query: ' . $sql . '<br>' . "\n";
                $error .= 'Error Code: ' . $errorInfo[1] . '<br>' . "\n";
                $error .= 'Error Message: ' . $errorInfo[2] . '<br>' . "\n";
            }
            $this->error = $error;
            if (!$this->console)
                Core::errorOut(500, $error);
        }
        return $this;
    }

    /**
     * Inserts a single row in the selected table
     *
     * @param array $data with columnName => value pairs
     * @return integer Last Insert Id or 0
     */

    public function insertOne($data = [])
    {
        if (!empty($data)) {
            $sql = "INSERT INTO `" . $this->_table . "` (`" .
                implode("`, `", array_keys($data)) . "`) VALUES (:" .
                implode(" , :", array_keys($data)) .
                ")";
            $this->query($sql, $data);
            return $this->_last_insert_id;
        } else return 0;
    }

    /**
     * Deletes one or more rows from the selected table
     *
     * @param array $condition Accepts an array with columnName => value pairs or a pure string to condition by it.
     * @return integer number of deleted rows
     */

    public function delete($condition = [])
    {
        $sql = "DELETE FROM `" . $this->_table . "`";
        $params = [];
        if (!empty($condition)) {
            $conditionString = '';
            if (is_array($condition)) {
                $params = $condition;
                $conditions = [];
                foreach ($condition as $column => $value) {
                    $conditions[] = "`" . $column . "`=:" . $column;
                }
                $conditionString .= '(' . implode(" AND ", $conditions) . ')';
            } else {
                $conditionString = $condition;
            }
            $sql .= ' WHERE ' . $conditionString;
        } else {
            $error = 'For security reasons you cannot delete all rows in a table without properly specifying a condition.' .
                'You can use $db->delete("1=1") if you are certain you want to erase all records.';
            if (!$this->console)
                Core::errorOut(200, $error);
            $this->error = $error;
            return false;
        }
        $this->query($sql, $params);
        return $this->_count;
    }

    public function getById($id = 0, $idColumn = 'id', $returnArray = true)
    {
        return $this->getRow("`" . $idColumn . "`=" . $id, $returnArray);
    }

    public function getRow($condition = [], $returnArray = true)
    {
        $this->getRows($condition, '', 1, 1, $returnArray);
        if (!empty($this->_results)) {
            return $this->_results[0];
        } else {
            return false;
        }
    }

    /**
     * Main function for getting data from a table, getRow and getById rely on it
     *
     * @param string $table The table name
     * @param array $condition Accepts an array with columnName => value pairs or a pure string to condition by it.
     * @param string $order The order string
     * @param int $page 0 will return all rows
     * @param int $perPage if $page != 0 $perPage will return only up to $perPage items
     * @param bool $returnArray should we return an associative array or
     *
     * @return array of associative arrays (or stClass objects) can be empty
     */

    public function getRows($condition = [], $order = '', $page = 0, $perPage = 50, $returnArray = true)
    {
        $sql = "SELECT * FROM `" . $this->_table . "`";
        $params = [];
        if (!empty($condition)) {
            $conditionString = '';
            if (is_array($condition)) {
                $params = $condition;
                $conditions = [];
                foreach ($condition as $column => $value) {
                    $conditions[] = "`" . $column . "`=:" . $column;
                }
                $conditionString .= '(' . implode(" AND ", $conditions) . ')';
            } else {
                $conditionString = $condition;
            }
            $sql .= ' WHERE ' . $conditionString;
        }
        if (!empty($order)) {
            $sql .= ' ORDER BY ' . $order;
        }
        if ($page > 0) {
            $sql .= ' LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;
        }
        $this->query($sql, $params, $returnArray);
        return $this->_results;
    }


}