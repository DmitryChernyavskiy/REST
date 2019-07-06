<?
    function addToArray($arr1, $arr2)
    {
        $num = 0;
        foreach($arr1 as $i)
        {
            $num++;
            $str = "".$num;
            foreach($i as $key=>$val)
            {
                $str .=  " ".$key."=".$val."; ";
            }
            $arr2[] = $str;
        }
        return $arr2;
    }