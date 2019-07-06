<?php
class QuerySQL
{
        private $tableName, $limit, $filds, $conditions;
        private $join_conditions, $group_filds, $functions, $query="";
        public $params, $errortext = "";
        
        function __construct()
        {
                $this->limit=0; 
                $this->filds=array();
                $this->conditions=array();
                $this->join_conditions=array();
                $this->params=array();
                $this->group_filds=array();
                $this->functions=array();                      
        }
        
        public function clearQuery($fullReset=false)
        {
                $this->limit=0;
                array_splice($this->filds, 0);
                array_splice($this->conditions, 0);
                array_splice($this->join_conditions, 0);
                
                if ($fullReset)
                {
                        $this->query="";
                        //$this->errortext = '';
                        array_splice($this->params, 0);
                        array_splice($this->group_filds, 0);
                        array_splice($this->functions, 0);
                        //array_splice($this->conditions, 0);
                }
                
                return $this;
        }
        
        protected function stringVerification($str, $errortext)
        {
                if((!is_string($str)) || $str == "" || $str == "*")
                {
                        $this->errortext .= $errortext."".$tableName."; ";
                }
        }
        
        public function setTableName($tableName)
        {
                $this->stringVerification($tableName, "Invalid table name");
                $this->tableName = $tableName;
                
                return $this;
        }
        
        public function setLimit($limit)
        {
                $this->limit = (int)$limit;
                
                return $this;
        }
        
        public function setFild($fild, $value = "")
        {
                $this->stringVerification($fild, "Invalid fild name");
                $name_param = ":param".count($this->params);
                $this->filds[$fild] = $name_param;
                if($value != "")
                {
                    $this->params[$name_param]=$value;
                }
                return $this;
        }

        public function setConditions($fild, $value)
        {
                $this->stringVerification($fild, "Invalid condition fild name");
                $name_param = ":param".count($this->params);
                $this->conditions[$fild] = $name_param;
                $this->params[$name_param]=$value;
                //print_r($this->params);
                //print_r($this->conditions);
                
                return $this;
        }

        //this function is not verivicetion!
        public function setOrder($fild, $desc=false)
        {
                $direct = ($desc ? "DESC" : "ASC");
                $this->query = str_replace("/*add_order2*/", ", ".$fild." ".$direct."/*add_order2*/", $this->query);
                $this->query = str_replace("/*add_order*/", "ORDER BY ".$fild." ".$direct."/*add_order2*/", $this->query);
                
                return $this;
        }

        //this function is not verivicetion!
        public function setHaving($synonym, $value)
        {
                $name_param = ":param".count($this->params);
                $this->query = str_replace("/*add_having2*/", ", ".$synonym."=".$name_param."/*add_having2*/", $this->query);
                $this->query = str_replace("/*add_having*/", "HAVING ".$synonym."=".$name_param."/*add_having2*/", $this->query);
                $this->params[$name_param]=$value;;

                return $this;
        }

        //this function is not verivicetion!
        public function setJoinConditions($Conditions)
        {
                $this->join_conditions[] = $Conditions;
                
                return $this;
        }
        
        protected function addSynonym($name, $fild, $synonym)
        {
                $this->stringVerification($fild, "Invalid fild name");
                $synonym = ($synonym == "" ? $fild."_".$name : $synonym);
                //$this->stringVerification($synonym, "Invalid synonym name");
                $str = $name."(".$this->tableName.".".$fild.") as ".$synonym;
                $this->functions[] = $str;
                
                return $this;
        }

        public function setSum($fild, $synonym = "")
        {
                return $this->addSynonym("sum", $fild, $synonym);
        }
        
        public function setCount($fild, $synonym = "")
        {
                return $this->addSynonym("count", $fild, $synonym);
        }
        
        public function setMin($fild, $synonym = "")
        {
                return $this->addSynonym("min", $fild, $synonym);
        }
        
        public function setMax($fild, $synonym = "")
        {
                return $this->addSynonym("max", $fild, $synonym);
        }
        
        public function group()
        {
                $add_column = "";
                foreach($this->functions as $key=>$val)
                {
                        $add_column .= ", ".$val."";
                }
                $add_column .= " /*add_column*/";
                $this->query = str_replace("/*add_column*/", $add_column, $this->query);

                $group = "";
                $separator = " GROUP BY ";
                foreach($this->group_filds as $key=>$val)
                {
                    $group .= $separator.$val;
                    $separator = ", ";;
                }
                if ($group != "")
                {
                        $group .= " /*add_having*/";
                }
                $this->query = str_replace("/*add_group*/", $group, $this->query);
                
                return $this;
        }
        
        public function getParams()
        {
                return $this->params; 
        }
        
        private function getQueryCondition($firstCondition = true)
        {
                $query = "";
                if(count($this->conditions)>0)
                {
                        $query = ($firstCondition ? " WHERE (" : " AND (");
                        $separator = "";
                        foreach($this->conditions as $key=>$val)
                        {
                                $query .= $separator."".$this->tableName.".".$key." = ".$val."";
                                $separator = " AND ";
                        }
                        $query .= ")";
                }
                elseif ($firstCondition)
                {
                        $query = " WHERE (TRUE)";
                }
                return $query." /*add_condition*/";
        }
        
        public function getQuery()
        {
                //clear comment
                $query = $this->query;
                for($i = 0; $i <= 5; $i++)
                {
                        $pos1 = strpos($query, "/*");
                        if (!$pos1)
                        {
                                break;
                        }
                        $pos2 = strpos($query, "*/");
                        $query = str_replace(substr($query, $pos1, $pos2-$pos1+2), "", $query);
                }
                //print_r($this->params);

                return $query;
        }
        
        private function setGroupFilds()
        {
                foreach($this->filds as $key=>$val)
                {
                        $this->group_filds[] =$this->tableName.".".$key."";
                }
        }
        
        public function select()
        {
                $query = "SELECT DISTINCT ";
                $separator = "";
                foreach($this->filds as $key=>$val)
                {
                        $query .= $separator."".$this->tableName.".".$key."";
                        $separator = ", ";
                }
                $query .= " /*add_column*/ FROM ".$this->tableName." /*add_table*/";
                $this->setGroupFilds();
                
                //where
                $query .= $this->getQueryCondition()." /*add_group*/ /*add_order*/";
                
                //limit
                if($this->limit > 0)
                {
                        $query .="LIMIT ".$this->limit; 
                }
                $this->query = $query;
                
                $this->clearQuery();
                
                return $this;
        }
        
        public function delete()
        {
                $query = "DELETE ".$this->tableName." FROM ".$this->tableName." /*add_table*/";
                
                //where
                $query .= $this->getQueryCondition();
                $this->query = $query;
                
                $this->clearQuery();
                
                return $this;
        }
        
        public function insert()
        {
                $query = "INSERT INTO ".$this->tableName." ";
                if(count($this->filds)>0)
                {
                        $query .= "(";
                        $queryVal = "";
                        $separator = "";
                        foreach($this->filds as $key=>$val)
                        {
                                $query .= $separator."".$this->tableName.".".$key."";
                                $queryVal .= $separator."".$val."";
                                $separator = ", ";
                        }
                        $query .= ") VALUES (".$queryVal.")";
                        $this->query = $query;
                }
                
                return $this;
        }
        
        public function update()
        {
                $query = "UPDATE ".$this->tableName." /*add_table*/ SET ";
                if(count($this->filds)>0)
                {
                        $separator = "";
                        foreach($this->filds as $key=>$val)
                        {
                                $query .= $separator."".$this->tableName.".".$key."=".$val."";
                                $separator = ", ";
                        }
                }
                //where
                $query .= $this->getQueryCondition();
                $this->query = $query;
                
                $this->clearQuery();
                
                return $this;
        }
        
        //the table being joined must already be set (inner join, left join......) before running this function
        private function addJoinParam($typeJoin, $join_conditions = true)
        {
                //insertion join with addition table
                $join = $typeJoin." ".$this->tableName;
                if ($join_conditions) //insertion codition of join
                {
                        $separator = " ON ";
                        foreach($this->join_conditions as $val)
                        {
                                $join .= $separator.$val;
                                $separator = " AND ";
                        }
                }
                $join .= " /*add_table*/";
                $this->query = str_replace("/*add_table*/", $join, $this->query);
                
                //insertion addional column (belonging addition table) - this only for function "SELECT"
                $add_column = "";
                foreach($this->filds as $key=>$val)
                {
                        $add_column .= ", ".$this->tableName.".".$key."";
                }
                $add_column .= " /*add_column*/";
                $this->query = str_replace("/*add_column*/", $add_column, $this->query);
                $this->setGroupFilds();
                                
                //insertion addional condition (belonging addition table)
                $add_condition .= $this->getQueryCondition(false);
                $this->query = str_replace("/*add_condition*/", $add_condition, $this->query);
                
                $this->clearQuery();
                
        }
        
        public function innerJoin()
        {
                //insertion join with addition table
                $this->addJoinParam("INNER JOIN");
                
                return $this;
        }
        
        public function leftJoin()
        {
                //insertion join with addition table
                $this->addJoinParam("LEFT JOIN");
                
                return $this;
        }
        
        public function rightJoin()
        {
                //insertion join with addition table
                $this->addJoinParam("RIGHT JOIN");
                
                return $this;
        }
        
        public function crossJoin()
        {
                //insertion join with addition table
                $this->addJoinParam("CROSS JOIN", false);
                
                return $this;
        }
        
        public function naturalJoin()
        {
                //insertion join with addition table
                $this->addJoinParam("NATURAL JOIN", false);
                
                return $this;
        }
}