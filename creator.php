<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
class Creator {
    private $con;
    private $servidor ;
    private $banco;
    private $usuario;
    private $senha;
    private $tabelas;

    function __construct() {
        $this->criaDiretorios();
        $this->conectar();
        $this->buscaTabelas();
        $this->ClassesModel();
        $this->ClasseConexao();
        $this->ClassesControl();
        $this->ClassesView();
        
    }
    function criaDiretorios() {
        $dirs = [
            "sistema",
            "sistema/model",
            "sistema/control",
            "sistema/view",
            "sistema/dao",
            "sistema/css"
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    header("Location:index.php?msg=0");
                }
            }
        }
        // Cria o arquivo CSS para as views
        $css = <<<EOT
body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
}
form {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    width: 300px;
}
input, select, button {
    margin-top: 10px;
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
button {
    background: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover {
    background: #45a049;
}
EOT;
        file_put_contents("sistema/css/estilos.css", $css);
    }
    function conectar() {
        $this->servidor=$_POST["servidor"];
        $this->banco=$_POST["banco"];
        $this->usuario=$_POST["usuario"];
        $this->senha=$_POST["senha"];
        try {
            $this->con = new PDO(
                "mysql:host=" . $this->servidor . ";dbname=" . $this->banco,
                $this->usuario,
                $this->senha
            );
        } catch (Exception $e) {
            header("Location:index.php?msg=1");
        }
    }
    function buscaTabelas(){
        $sql = "SHOW TABLES";
        $query = $this->con->query($sql);
        $this->tabelas = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    function buscaAtributos($nomeTabela){
        $sql="show columns from ".$nomeTabela;
        $atributos = $this->con->query($sql)->fetchAll(PDO::FETCH_OBJ);
        return $atributos;
    }
    function ClassesModel() {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $atributos=$this->buscaAtributos($nomeTabela);
            $nomeAtributos="";
            $geters_seters="";
            foreach ($atributos as $atributo) {
                $atributo=$atributo->Field;
                $nomeAtributos.="\tprivate \${$atributo};\n";
                $metodo=ucfirst($atributo);
                $geters_seters.="\tfunction get".$metodo."(){\n";
                $geters_seters.="\t\treturn \$this->{$atributo};\n\t}\n";
                $geters_seters.="\tfunction set".$metodo."(\${$atributo}){\n";
                $geters_seters.="\t\t\$this->{$atributo}=\${$atributo};\n\t}\n";
            }
            $nomeTabela=ucfirst($nomeTabela);
            $conteudo = <<<EOT
<?php
class {$nomeTabela} {
{$nomeAtributos}
{$geters_seters}
}
?>
EOT;
            file_put_contents("sistema/model/{$nomeTabela}.php", $conteudo);

        }
    }
    function ClasseConexao(){
        $conteudo = <<<EOT
<?php
class Conexao {
    private \$server;
    private \$banco;
    private \$usuario;
    private \$senha;
    function __construct() {
        \$this->server = '[Informe aqui o servidor]';
        \$this->banco = '[Informe aqui o seu Banco de dados]';
        \$this->usuario = '[Informe aqui o usuário do banco de dados]';
        \$this->senha = '[Informe aqui a senha do banco de dados]';
    }
    function conectar() {
        try {
            \$conn = new PDO(
                "mysql:host=" . \$this->server . ";dbname=" . \$this->banco,\$this->usuario,
                \$this->senha
            );
            return \$conn;
        } catch (Exception \$e) {
            echo "Erro ao conectar com o Banco de dados: " . \$e->getMessage();
        }
    }
}
?>
EOT;
        file_put_contents("sistema/model/conexao.php", $conteudo);
    }

    function ClassesControl(){
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeClasse=ucfirst($nomeTabela);
            $conteudo = <<<EOT
<?php
require_once("../model/{$nomeClasse}.php");
require_once("../dao/{$nomeClasse}Dao.php");
class {$nomeClasse}Control {
    private \${$nomeTabela};
    private \$acao;
    private \$dao;
    public function __construct(){
       \$this->{$nomeTabela}=new {$nomeClasse}();
      \$this->dao=new {$nomeClasse}Dao();
      \$this->acao=\$_GET["a"];
      \$this->verificaAcao(); 
    }
    function verificaAcao(){}
    function inserir(){}
    function excluir(){}
    function alterar(){}
    function buscarId({$nomeClasse} \${$nomeTabela}){}
    function buscaTodos(){}

}
new {$nomeClasse}Control();
?>
EOT;
            file_put_contents("sistema/control/{$nomeTabela}Control.php", $conteudo);
        }

    }
    function ClassesView() {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $atributos = $this->buscaAtributos($nomeTabela);
            $campos = "";
            foreach ($atributos as $atributo) {
                if ($atributo->Key === "PRI") continue; // Ignora chave primária
                $type = (stripos($atributo->Type, 'int') !== false) ? 'number' : 'text';
                $campos .= "<label for='{$atributo->Field}'>{$atributo->Field}:</label>\n";
                $campos .= "<input type='{$type}' name='{$atributo->Field}' id='{$atributo->Field}' required>\n";
            }
            $form = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
<form method="POST" action="">
    {$campos}
    <button type="submit">Enviar</button>
</form>
</body>
</html>
EOT;
            file_put_contents("sistema/view/{$nomeTabela}Form.php", $form);
        }

    }
}

new Creator();