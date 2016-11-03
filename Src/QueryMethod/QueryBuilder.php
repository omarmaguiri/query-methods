<?php

namespace QueryMethod;


class QueryBuilder
{
    private $reflector;
    private $queryTree;
    private static $operations = [
        "" => "= ?",
        "Is" => "= ?",
        "Equals" => "= ?",
        "LessThan" => "< ?",
        "LessThanEqual" => "<= ?",
        "GreaterThan" => "> ?",
        "GreaterThanEqual" => ">= ?",
        "Not" => "<> ?",
        "Between" => "BETWEEN ? AND ?",
        "After" => "> ?",
        "Before" => "< ?",
        "IsNull" => "IS NULL",
        "NotNull" => "IS NOT NULL",
        "IsNotNull" => "IS NOT NULL",
        "Like" => "LIKE ?",
        "NotLike" => "NOT LIKE ?",
        "StartingWith" => "LIKE CONCAT(?, '%')",
        "EndingWith" => "LIKE CONCAT('%', ?)",
        "Containing" => "LIKE CONCAT('%', ?, '%')",
        "True" => "= true",
        "False" => "= false",
        "Distinct" => "DISTINCT",
        "OrderBy" => "ORDER BY",
        "Asc" => "ASC",
        "Desc" => "DESC",
        "GroupBy" => "GROUP BY",
        "Having" => "HAVING",
    ];

    /**
     * QueryBuilder constructor.
     * @param $object
     * @param $queryTree
     */
    public function __construct($object, $queryTree = null)
    {
        $this->reflector = new \ReflectionClass($object);
        $this->queryTree = $queryTree;
    }


    public function build(){
        if (is_null($this->queryTree))
            throw new \Exception("QueryBuilderException: The Query Tree Was Not Set");

        $table = strtolower($this->reflector->getShortName());
        $query = $this->selectClause()." FROM $table ".$this->whereClause()." ".$this->groupByClause()." ".$this->orderByClause();
        $query = trim(preg_replace("/\s+/", " ", $query));
        return $query;
    }

    /**
     * @param array $queryTree
     * @return self
     */
    public function setQueryTree($queryTree)
    {
        $this->queryTree = $queryTree;
        return $this;
    }

    private function selectClause()
    {
        $selectClause = $this->queryTree['Select'];
        $query = "SELECT ";

        if (is_null($selectClause))
            return $query."*";

//        $distinctColumn = "";
        foreach ($selectClause as $key => $value){
            if (!isset(static::$operations[$key]))
                throw new \Exception("Unknown Statement $key");

            if (static::$operations[$key] === "DISTINCT")
                return trim($query.static::$operations[$key]." * ");

//            $query .= static::$operations[$key]." $value";
//            $distinctColumn = $value;
        }
//        $properties = array_map(function($property){ return $property->getName(); }, $this->reflector->getProperties());
//        foreach ($properties as $property){
//            if ($property !== $distinctColumn)
//                $query .= ', '.$property;
//        }

        return trim($query);
    }
    private function whereClause()
    {
        $selectClause = $this->queryTree['Where'];
        
        if (is_null($selectClause))
            return "";

        $query = "WHERE";
        foreach ($selectClause as $clause){
            $query .= ' '.$clause['column'].' '.static::$operations[$clause['operation']].' '.$clause['conjunction'];
        }
        return trim($query);
    }
    private function groupByClause()
    {
        $groupByClause = $this->queryTree['GroupBy'];
        if (is_null($groupByClause))
            return "";

        $query = "GROUP BY ";
        foreach ($groupByClause['columns'] as $column){
            $query .= $column.', ';
        }
        return rtrim($query, ', ');
    }
    private function orderByClause()
    {
        $orderByClause = $this->queryTree['OrderBy'];
        if (is_null($orderByClause))
            return "";

        $query = "ORDER BY ";
        foreach ($orderByClause as $clause){
            $query .= $clause['column'].((!empty($clause['direction'])) ? ' '.$clause['direction']: '').', ';
        }
        return rtrim($query, ', ');
    }
}