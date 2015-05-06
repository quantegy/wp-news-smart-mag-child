<?php
class News_DBO extends PDO {
    
    private static $instance;
    
    public function __construct($host, $dbname, $user, $pass) {
        try {
            parent::__construct('mysql:host='.$host.';dbname='.$dbname, $user, $pass);
        } catch(PDOException $e) {
            exit($e->getMessage());
        } catch(Exception $e) {
            exit($e->getMessage());
        }
        
        self::$instance = $this;
    }
    
    public static function getInstance() {
        return self::$instance;
    }
}