<?php 


    function prettyArray(array $value)
    {
        echo "<pre>";
        print_r($value);
        echo "</pre>";
    } 

    function usortSituacion($a, $b) {

            if ($a['situacion']>$b['situacion']) {
                return 1;
            }elseif ($a['situacion'] == $b['situacion']) {
                return 0;
            }
            elseif ($a['situacion'] < $b['situacion']) {
                return -1;
            }
        }

 ?>
