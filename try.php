<?php
    require "func.php";
    function vortex(array $arr) {
        $r = [];
        $s = [];
        $i = count($arr);
        while ($i > 0){
            $ran = rand(0, count(array_keys($arr)) - 1);
            array_push($r, $arr[array_keys($arr)[$ran]]);
            array_push($s, strval(array_keys($arr)[$ran]));
            unset($arr[array_keys($arr)[$ran]]);
            $i -= 1;
        }
        return [$r, $s];
    }
    function disvortex(array $arr, array $key) {
        if(count($arr) === count($key)){
            $r = [];
            while (count($r) <= count($arr)) {
                array_push($r, null);
            }
            foreach ($key as $i => $value) {
                $r[$value] = $arr[$i];
            }
            return $r;
        }
        return [];
    }
    if(isset($_POST['vortex'])){
        $v = vortex((str_split($_POST['vortex'])));
        echo json_encode([join("", $v[0]), $v[1]]);
    }
    if(isset($_POST['vortexed']) && isset($_POST['key'])){
    echo join("", disvortex(str_split($_POST['vortexed']), explode(",", $_POST['key'])));
    }
?>