<?php

namespace QueryMethod;


use \QueryMethod\Utilities\DbConnector;

class Repository
{
    private $reflector;
    private $parser;
    private $queryBuilder;
    private $pdo;

    public $queryString;
    /**
     * Repository constructor.
     * @param $object
     */
    public function __construct($object)
    {
        $this->pdo = DbConnector::getPDO();
        $this->parser = new MethodNameParser();
        $this->queryBuilder = new QueryBuilder($object);
        $this->reflector = new \ReflectionClass($object);
    }

    public function __call($name, $arguments)
    {
        $queryTree = $this->parser->setMethodName($name)->parser();
        $this->queryString = $this->queryBuilder->setQueryTree($queryTree)->build();
        $results = $this->executeQueryString($arguments);

        $objects = [];
        foreach ($results as $result){
            $obj = $this->reflector->newInstance();
            foreach ($result as $key => $value){
                $setter = 'set'.ucfirst(strtolower($key));
                if ($this->reflector->hasMethod($setter)){
                    $method = $this->reflector->getMethod($setter);
                    $method->invoke($obj, $value);
                }
            }
            $objects[] = $obj;
        }

        if (count($objects) === 1){
            return array_shift($objects);
        }
        return $objects;
    }
    
    private function executeQueryString(array $arguments){
        try{
            $stmt = $this->pdo->prepare($this->queryString);
            $stmt->execute($arguments);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $results = $stmt->fetchAll();
            $stmt->closeCursor();
            return $results;
        }
        catch (\PDOException $e){
            throw new \Exception("RepositoryException: ".$e->getMessage(), $e->getCode());
        }
    }
}
