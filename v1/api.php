<?php
require_once("class.rest.php");
require_once("class.mysql.php");
require_once("../index.php");



class API extends REST {
    public $data = "";
    const DB_SERVER = "localhost";
    const DB_USER = "root";
    const DB_PASSWORD = "JoseCuervo";
    const DB = "tutorvirtual";

    private $db = NULL;

    public function __construct()
    {
        parent::__construct();// Init parent contructor
        $this->dbConnect();// Initiate Database connection
    }

    //Database connection
    private function dbConnect()
    {
        $this->db = new MySQL(self::DB, self::DB_USER, self::DB_PASSWORD,self::DB_SERVER);
    }

    //Public method for access api.
    //This method dynmically call the method based on the query string
    public function processApi()
    {

        // print_r($_REQUEST);
        $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
        if((int)method_exists($this,$func) > 0)
            $this->$func();
        else
            $this->response('',404);
        // If the method not exist with in this class, response would be "Page not found".
    }

     private function verifyToken($token)
    {

        if (is_null($token)) {
            return false;
        }

        $hash = substr($token, 8);
        $matricula = substr($token, 0, 8);

        $loginQuery = $this->db->ExecuteSQL(
                "SELECT matricula FROM alumno where matricula = '$matricula' AND hash = '$hash' LIMIT 1");

        if ($loginQuery && !is_bool($loginQuery)) {
            return true;
        }else{
            return false;
        }
    }

    private function login()
    {
        // Cross validation if the request method is POST else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            $this->response('',406);
        }

        if (!isset($this->_request['matricula'])) {
            $matricula = NULL;
        }else{
            $password = $this->_request['pwd'];
        }


        if (!isset($this->_request['pwd'])) {
            $password = NULL;
        }else{
            $matricula = $this->_request['matricula'];
        }

        // Input validations
        if(!empty($matricula) and !empty($password)){

            $matricula  = str_replace("a", "", $matricula);

            $password = sha1($password);

            $loginQuery = $this->db->ExecuteSQL(
                sprintf("SELECT matricula, nombre, hash FROM alumno where matricula = '%s' AND password = '%s' LIMIT 1",
                    mysql_real_escape_string($matricula),
                    mysql_real_escape_string($password)));

            if ($loginQuery) {
                if (is_bool($loginQuery)) {
                    $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
                    $this->response($this->json($error), 400);
                }else{

                    $result = array("nombre" => $loginQuery['nombre'],
                        "hash" => $loginQuery['matricula'].$loginQuery['hash']);

                    $this->response($this->json($result), 200);

                }
            }

        }

        // If invalid inputs "Bad Request" status message and reason
        $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
        $this->response($this->json($error), 400);
    }

    private function test()
    {
        // Cross validation if the request method is POST else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            $this->response('',406);
        }

        if (!isset($this->_request['token'])) {
            $token = NULL;
        }else{
            $token = $this->_request['token'];
        }




        if (!$this->verifyToken($token)) {
            // If invalid inputs "Bad Request" status message and reason
            $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
            $this->response($this->json($error), 400);
        }

        $matricula = substr($token, 0, 8);

        //GIL



        $error = array('status' => "Correct", "msg" => "You have access.");
        $this->response($this->json($error), 400);
    }

    private function getCarga()
    {
        // Cross validation if the request method is POST else it will return "Not Acceptable" status
        if($this->get_request_method() != "POST")
        {
            $this->response('',406);
        }

        if (!isset($this->_request['token'])) {
            $token = NULL;
        }else{
            $token = $this->_request['token'];
        }

        if (!$this->verifyToken($token)) {
            // If invalid inputs "Bad Request" status message and reason
            $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
            $this->response($this->json($error), 400);
        }

        $this->response($this->json(getFailed(substr($token, 0, 8), $this->db)), 200);


    }


    //Encode array into JSON
    private function json($data)
    {
        if(is_array($data)){
            return json_encode($data);
        }
    }
}

// Initiiate Library
$api = new API;
$api->processApi();
?>
