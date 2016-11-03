<?php

namespace QueryMethod;

class MethodNameParser
{
    private $methodName;
    private static $operations = [
        "Is",
        "Equals",
        "LessThan",
        "LessThanEqual",
        "GreaterThan",
        "GreaterThanEqual",
        "Not",
        "Between",
        "After",
        "Before",
        "IsNull",
        "NotNull",
        "IsNotNull",
        "Like",
        "NotLike",
        "StartingWith",
        "EndingWith",
        "Containing",
        "True",
        "False",
        "Distinct",
        "OrderBy",
        "Asc",
        "Desc",
        "GroupBy",
        "Having",
    ];

    /**
     * @param mixed $methodName
     * @return self
     * @throws
     */
    public function setMethodName($methodName)
    {
        if (!preg_match("/^find(.*)By(.*)$/", $methodName)){
            throw new \Exception("Parsing Exception: Not acceptable Name Of Method");
        }
        $this->methodName = $methodName;
        return $this;
    }

    public function parser(){
        preg_match("/^find(.*)(?<!Group|Order)By(.*)$/", $this->methodName, $arg);
        $sql["Select"] = $this->selectClause($arg[1]);

        $arg = preg_replace("/(Group|Order)By.*/", '', $arg[2]);
        $sql["Where"] = $this->whereClause($arg);

        preg_match("/GroupBy(.*)OrderBy.*|GroupBy(.*)$/", $this->methodName, $arg);
        $sql["GroupBy"] = $this->groupByClause(array_pop($arg));

        preg_match("/OrderBy(.*)GroupBy.*|OrderBy(.*)$/", $this->methodName, $arg);
        $sql["OrderBy"] = $this->orderByClause(array_pop($arg));

        return $sql;
    }

    private function selectClause($select_string)
    {
        $select_string = trim($select_string);
        if (empty($select_string))
            return;

        $selectClause = explode('_', ltrim(preg_replace('/[A-Z]/', '_$0', $select_string), '_'));
        return (count($selectClause) === 1) ? [$selectClause[0] => "*"] : [$selectClause[0] => lcfirst($selectClause[1])];
    }
    private function whereClause($where_string)
    {
        $where_string = trim($where_string);

        if (empty($where_string))
            return;

        $columns = explode('@', preg_replace("/(And|Or)/", '$0@', $where_string));

        $whereClause = [];
        foreach ($columns as $column){
            $logicalOperator = "";
            if (preg_match("/(.*)(And|Or)/", $column, $arg)) {
                $column = $arg[1];
                $logicalOperator = strtoupper($arg[2]);
            }
            $columnAndOperation = explode('@', ltrim(preg_replace('/[A-Z]/', '@$0', $column), '@'));

            $clause["column"] = lcfirst(array_shift($columnAndOperation));
            $operation = implode("", $columnAndOperation);
            if (in_array($operation, static::$operations)){
                $clause["operation"] = $operation;
            }else{
                $clause["operation"] = "";
                if (preg_match("/(.*)_/", $clause['column'], $m)){
                    $clause["column"] = $m[1].'.'.lcfirst($operation);
                }
                else
                    $clause["column"] = $clause["column"].$operation;
            }
            $clause["conjunction"] = $logicalOperator;
            $whereClause[] = $clause;
        }
        return $whereClause;
    }
    private function orderByClause($orderBy_string)
    {
        $orderBy_string = trim($orderBy_string);
        if (empty($orderBy_string))
            return;
        $clauses = ltrim(preg_replace("/[A-Z]/", '_$0', $orderBy_string), '_');
        $clauses = explode('_',preg_replace("/_(Asc|Desc)/", '$1', $clauses));
        $orderByClause = [];
        foreach ($clauses as $clause){
            $arg =  explode('_', ltrim(preg_replace('/[A-Z]/', '_$0', $clause), '_'));
            $_clause['column'] = lcfirst($arg[0]);
            $_clause['direction'] = isset($arg[1]) ? $arg[1] : "";
            $orderByClause[] = $_clause;
        }
        return $orderByClause;
    }
    private function groupByClause($groupBy_string)
    {
        $groupBy_string = trim($groupBy_string);
        if (empty($groupBy_string))
            return;

        $groupByClause['columns'] = array_map(function($value){return lcfirst($value);}, explode('_', ltrim(preg_replace('/[A-Z]/', '_$0', $groupBy_string), '_')));
        return $groupByClause;
    }
}