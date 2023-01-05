<?php 
include_once('config.php');

class Database{
    
    public $que;
    private $servername=BD_SERVIDOR;
    private $username=BD_USUARIO;
    private $password=BD_SENHA;
    private $dbname=BD_BANCO;
    private $result=array();
    private $mysql='';
    
    public function __construct(){
        $strcon = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname); 

            $this->mysql = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname); 
        }

        public function insert($table,$para){
            $table_columns = implode(',', array_keys($para));
            $table_value = implode("','", $para);

            $sql="INSERT INTO $table($table_columns) VALUES('$table_value')";

            $result = mysqli_query($this->mysql, $sql);
        }

        public function update($table,$para,$id){
            $args = array();

            foreach ($para as $key => $value) {
                $args[] = "$key = '$value'"; 
            }

            $sql="UPDATE  $table SET " . implode(',', $args);

            $sql .=" WHERE $id";

            $result = mysqli_query($this->mysql, $sql);
        }

        public function delete($table,$id){
            $sql="DELETE FROM $table";
            $sql .=" WHERE $id ";
            $sql;
            $result = mysqli_query($this->mysql, $sql);
        }

        public $sql;

        public function select($table,$rows="*",$where = null){
            if ($where != null) {
                $sql="SELECT $rows FROM $table WHERE $where";
            }else{
                $sql="SELECT $rows FROM $table";
            }

            $this->sql = $result = mysqli_query($this->mysql, $sql);
        }

        public function lastID($table){
            $sql = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";

            $this->sql = $result = mysqli_query($this->mysql, $sql);
        }

        public function __destruct(){
            $this->mysql->close();
        }
    }

//      # Cria o ponto no banco de dados.
//      $insert = new Database();
//      $insert->insert("ponto", ['usuario' => 'TEST', 'distintivo' => "999", 'status' => 'ABERTO', 'entrada' => '0000-00-00', 'saida' => '0000-00-00']);


?>