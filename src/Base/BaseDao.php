<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:20
 */
namespace Dai\Framework\Base;

class BaseDao extends \Phalcon\Mvc\Model
{
    /**
     * @param $sid
     * @param $param
     * @return mixed
     * @throws BaseException
     */
    public function execute($sid, $param)
    {
        $dirArr = explode(".", $sid);
        if( count($dirArr) != 3 ){
            throw new BaseException (BaseException::INTER_ERROR);
        }
        $file = APP_PATH."/config/sqlmap/".$dirArr[0]."/". $dirArr[1].".ini";

        try{
            $iniReader = new \Phalcon\Config\Adapter\Ini($file);
            $sql = $iniReader[ $dirArr[2] ]->sql;
            $table = $iniReader->table->name;
            $sql = str_replace("%table", $table, $sql);
            if( is_array($param) && isset($param['select']) ){
                $sql = str_replace("%SELECT", $param['select'], $sql);
                unset($param['select']);
            }
        }catch (\Exception $e){
            throw new BaseException(BaseException::INTER_ERROR, $e->getMessage());
        }

        $functionPre = explode("_", $dirArr[2]);
        try{
            $this->pdo = $this->getDI()->getShared('db');
            $function = $functionPre[0]."Base";
            return $this->$function($sql, $param);
        }catch (\Exception $e){
            throw new BaseException(BaseException::INTER_ERROR, $e->getMessage());
        }
    }

    /**
     * @param $sql
     * @param $data
     * @return bool
     */
    public function insertBase($sql, $data)
    {
        $data = json_decode(json_encode($data), true);
        $insertKey = [];
        $insertKeyPre = [];
        $insertData = [];
        foreach($data as $key => $value){
            if( $value != null){
                $insertKey[] = $key;
                $insertKeyPre[] = ":".$key;
                $insertData[":".$key] = $value;
            }
        }
        $insertStr = "(". implode(",", $insertKey) .") VALUES (". implode(",", $insertKeyPre) .")";
        $sql = str_replace("%INSERT", $insertStr, $sql);
        $ret = $this->execSql($this->pdo, $sql, $insertData);
        if( $ret == false){
            return false;
        }
        return $this->pdo->lastInsertId();
    }

    /**
     * @param $sql
     * @param $data
     * @return mixed
     */
    public function editBase($sql, $data)
    {
        $data = json_decode(json_encode($data), true);
        $updateKeys = [];
        $updateValues = [];
        foreach($data as $key => $value){
            if( $value != null){
                $updateKeys[] = $key ." =  $".$key;
                $updateValues[$key] = $value;
            }
        }
        $sql = str_replace("%UPDATE", implode(",", $updateKeys), $sql);
        return $this->execSql($this->pdo, $sql, $updateValues);
    }

    /**
     * @param $sql
     * @param $data
     * @return mixed
     */
    public function deleteBase($sql, $data)
    {
        return $this->execSql($this->pdo, $sql, $data);
    }

    /**
     * @param $sql
     * @param $data
     * @return mixed
     */
    public function selectBase($sql, $data)
    {
        $selectData = [];
        foreach ($data as $key => $item) {
            if( $item != null){
                $selectData["$key"] = $item;
            }
        }
        return $this->querySql($this->pdo, $sql, $selectData);
    }


    /**
     * @param $table
     * @param $sql
     * @param $data
     * @return int|mixed
     */
    public function totalBase($table, $sql, $data)
    {
        $data['table'] = $table;
        $pdo = $this->getDI()->getShared('db');
        $res =  $this->querySql($pdo, $sql, $data);
        return intval($res[0]['cnt']);
    }

    /**
     * @param $pdo
     * @param $sqlPre
     * @param $binds
     * @return mixed
     * @throws BaseException
     */
    private function querySql($pdo, $sqlPre, $binds)
    {
        if( Trace::getInstance()->getValid()== 1) {
            $debugStr = $this->getDebugStr($sqlPre, $binds);
            $key = $sqlPre.time().rand(1,100000);
            Trace::getInstance()->add($key, $debugStr, []);
        }

        try{
            $stmt = $pdo->prepare( $sqlPre );
            $res = $stmt ->execute( $binds);
            if($res != false){
                $stmt->setFetchMode(\Phalcon\DB::FETCH_ASSOC);
                $res =  $stmt->fetchAll();
            }

            if( Trace::getInstance()->getValid()== 1) {
                Trace::getInstance()->Attach($key, $res);
            }
            return $res;
        }catch ( \Exception $e ){
            if(  Trace::getInstance()->getValid() == 1) {
                Trace::getInstance()->Attach($key, [$e->getCode(), $e->getMessage()] );
            }
            throw new BaseException( BaseException::DB_ERROR, $e->getMessage() );
        }
    }

    /**
     * @param $pdo
     * @param $sqlPre
     * @param $binds
     * @return mixed
     * @throws BaseException
     */
    private function execSql($pdo, $sqlPre, $binds)
    {
        if( Trace::getInstance()->getValid()== 1) {
            $debugStr = $this->getDebugStr($sqlPre, $binds);
            $key = $sqlPre.time().rand(1,100000);
            Trace::getInstance()->add($key, $debugStr, []);
        }

        try{
            $stmt = $pdo->prepare( $sqlPre );
            $res = $stmt ->execute( $binds);
            if( Trace::getInstance()->getValid()== 1) {
                Trace::getInstance()->Attach($key, $res);
            }
            return $res;
        }catch ( \Exception $e ){
            if(  Trace::getInstance()->getValid() == 1) {
                Trace::getInstance()->Attach($key, [$e->getCode(), $e->getMessage()] );
            }
            throw new BaseException( BaseException::DB_ERROR, $e->getMessage() );
        }
    }


    /**
     * @param $sql
     * @return mixed
     * @throws BaseException
     */
    public function execRaw($sql)
    {
        if( Trace::getInstance()->getValid()== 1) {
            $key = $sql.time().rand(1,100000);
            Trace::getInstance()->add($key, $sql, []);
        }
        try{
            $result = $this->getDI()->getShared('db')->execute($sql);
            if( Trace::getInstance()->getValid()== 1) {
                Trace::getInstance()->Attach($key, $result);
            }
            return $result;
        }catch (\Exception $e ) {
            if( Trace::getInstance()->getValid()== 1) {
                $key = $sql." ;".time();
                Trace::getInstance()->Attach($key, $e->getMessage());
            }
            throw new BaseException( BaseException::DB_ERROR );
        }
    }

    /**
     * @param $sql
     * @return mixed
     * @throws BaseException
     */
    public function queryRaw($sql)
    {
        try{
            $result = $this->getDI()->getShared('db')->query($sql);
            $result->setFetchMode(\Phalcon\DB::FETCH_ASSOC);
            $resultArr = $result->fetchAll();
            if( Trace::getInstance()->getValid()== 1) {
                $key = $sql." ;".time();
                Trace::getInstance()->add($key, $sql, $resultArr );
            }
            return $resultArr;
        }catch (\Exception $e ) {
            if( Trace::getInstance()->getValid()== 1) {
                $key = $sql." ;".time();
                Trace::getInstance()->add($key, $sql, $e->getMessage());
            }
            throw new BaseException( BaseException::DB_ERROR );
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function trimBase($data)
    {
        $classDaoName = get_called_class();
        $reflectionDao = new \ReflectionClass ( $classDaoName );
        $commentDao = $reflectionDao->getDocComment();
        $className = \Dai\Framework\Library\Annotations::getCommentValue($commentDao, "dataObject");

        $object = new $className();
        $reflection = new \ReflectionClass ( $className );


        foreach ( $reflection->getProperties() as $property) {
            $propertyName = $property->name;
            if( ! isset($data[$propertyName]) ) {
                continue;
            }

            $value = $data[$propertyName];

            $reflectionProperty = new \ReflectionProperty($className, $propertyName);
            $comment = $reflectionProperty->getDocComment();
            $type = \Dai\Framework\Library\Annotations::getCommentValue($comment, 'type' );

            if( $type === "Int") {
                $object->$propertyName = intval($value);
            }elseif($type === "Double") {
                $object->$propertyName = doubleval($value);
            }elseif($type === "Json") {
                $object->$propertyName = json_decode($value);
            }else {
                $object->$propertyName = trim($value);
            }
        }
        return $object;
    }

    /**
     * @param $sqlPre
     * @param $binds
     * @return mixed
     */
    protected function getDebugStr($sqlPre, $binds){
        $debugSql = $sqlPre;
        foreach($binds as $key => $value){
            $debugSql = str_replace(":$key", "'$value'", $debugSql);
        }
        return $debugSql;
    }

    /**
     * @return mixed
     */
    public function begin()
    {
        return $this->getDI()->getShared('db')->query("BEGIN");
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        return $this->getDI()->getShared('db')->query("COMMIT");
    }

    /**
     * @return mixed
     */
    public function rollback()
    {
        return $this->getDI()->getShared('db')->query("ROLLBACK");
    }
}