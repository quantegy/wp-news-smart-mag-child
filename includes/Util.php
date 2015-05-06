<?php
class Util {
    public static function out($v) {
        print '<pre style="text-align:left; color:green; background-color:black; padding:10px;">';
        var_dump($v);
        print '</pre>';
    }
}