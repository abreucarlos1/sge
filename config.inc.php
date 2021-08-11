<?php
/*

	Arquivo de configurações

	Versão 0 --> 20/05/2021 - Carlos Abreu

*/

@ini_set('display_errors', 1);

@ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE | E_ALL);

@ini_set('default_charset', 'UTF-8');

$host = explode(":",$_SERVER['HTTP_HOST']);

if($host[0]!='localhost')
{

	define('DATABASE',"epiz_28706689_002");

	define('DBHOST',"sql203.epizy.com");

	define('DBUSER',"epiz_28706689");

	define('DBPASS',"nHJGCaSMw5u");

	define('DB',"epiz_28706689_002");
}
else
{
	define('DATABASE',"db_sge");

	define('DBHOST',"127.0.0.1");
	
	define('DBUSER',"root");
	
	define('DBPASS',"root");
	
	define('DB',"mysql");
}

define('CONNPERMANENTE',FALSE); //DEFINE CONEXÃO COM DB PERMANENTE -- TER ATENÇÃO AQUI

define('ROOT_DIR', dirname(__DIR__)); //DIRETÓRIO RAIZ

define('BASEDIR', basename(__DIR__)); //DIRETÓRIO BASE SITE

define('SITE_PATH',ROOT_DIR.DIRECTORY_SEPARATOR.BASEDIR.DIRECTORY_SEPARATOR); //DIRETÓRIO COMPLETO

define('DEBUG',FALSE); //DEFINE A APRESENTAÇÃO DE ERROS NO MODAL ALERT

define('LOG',TRUE); //DEFINE A GRAVAÇÃO DE LOG DE ERROS

define('LOG_ACOES',array(TRUE,'I','A','E','L')); //DEFINE QUAIS LOGS SERÃO GRAVADOS (INCLUSAO/ALTERACAO/EXCLUSAO/LOGIN)

define('LOG_ACESSOS',TRUE); // Define a gravação de logs de acesso

define('LOG_SIZE',1024*1024*10); //DEFINE O TAMANHO DO ARQUIVO DE LOG (10 MB)

define('DIAS_LIMITE',90); //dias de limite para senhas

define('TAMANHO_SENHA',7); //tamanho padrão de senhas

define('CHAVE','SISTEMA_SGE'); //CHAVE DE ENCRIPTAÇÃO NO SISTEMA

define('PREFIXO_DOC_GED','SGE-'); ///PREFIXO UTILIZADO NO GED

define('PREFIXO_PROPOSTAS','SGE-PT-'); ///PREFIXO UTILIZADO NA PROPOSTAS TÉCNICAS

define('NOME_EMPRESA','PROJETO PILOTO'); ///NOME DA EMPRESA PARA APRESENTAR NOS RELATÓRIOS

define('NOME_SISTEMA','SGE - Sistema Gerencial de Engenharia'); ///NOME DO SISTEMA A SER APRESENTADO NAS TELAS

define('CIDADE','CIDADE'); ///NOME DA CIDADE DA EMPRESA PARA APRESENTAR NOS RELATÓRIOS

define('MSGINCLUI','Registro cadastrado com sucesso.'); //MENSAGEM DE SUCESSO NA INCLUSÃO

define('MSGALTERA','Registro alterado com sucesso.'); //MENSAGEM DE SUCESSO NA ALTERAÇÃO

define('MSGEXCLUI','Registro excluído com sucesso.'); //MENSAGEM DE SUCESSO NA ALTERAÇÃO

define('MSGEXISTE','Registro já existente.'); //MENSAGEM DE REGISTRO EXISTENTE

define('MSGPREENCHE','Os campos devem ser preenchidos.'); //MENSAGEM DE REGISTRO EXISTENTE

//DEFINE A PAGINA CHAMADORA

define('PAGINA',$_SERVER['REQUEST_URI']);

$uri = explode('/', $_SERVER['REQUEST_URI']);

//DEFINE NOME HOST
define('HOST', $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST']:gethostname());

//DEFINE ROOT WEB
define('ROOT_WEB','http://'.HOST.'/');

$ocorrencias = substr_count(PAGINA,"/");

//se localhost, retira 1 nivel
if(stripos(HOST, "localhost") !== false)
{
	$ocorrencias--;

	define('PAGE_ROOT',ROOT_WEB.BASEDIR);
}
else
{
	define('PAGE_ROOT','http://'.HOST);
}

//monta o path relativo conforme o nivel da pagina chamadora

//ex: 1º nivel --> ./ 

//2º nivel --> ./../

// e assim sucessivamente

$test = "";

for($i=1;$i<$ocorrencias;$i++)
{
	$test .= "../";
}

define('DIR_PATH',$test);

if(strtoupper(PHP_OS)=='LINUX')
{	
	$raiz = '/';
}
else
{
	$raiz = 'C:\\';
}

define('DIRETORIO_ARQUIVOS','arquivos_sistema');

//DEFINE O DIRETORIO RAIZ DA MONTAGEM (GUARDA DE ARQUIVOS)

$diretorio = SITE_PATH . DIRETORIO_ARQUIVOS . DIRECTORY_SEPARATOR;

define('MOUNT_DIR',$diretorio);

//AMBIENTE 2 - PRODUÇÃO / 1 - TESTES

define('AMBIENTE',2);

define('AMBIENTE_EMAIL',1);

define('ENVIA_EMAIL',FALSE);

define('DOMINIO','dominio.com.br');

define('HOST_MAIL',"smtp.".DOMINIO);

define('FROM_NAME', "SUE");

define('FROM_MAIL', "mail@".DOMINIO);

define('SUPORTE_MAIL', "ti@".DOMINIO);

define('SISTEMAS_MAIL', "ti@".DOMINIO);

//Apenas enquanto estivermos desenvolvendo, poderemos usar estes
define('TI', "ti@".DOMINIO);

//DEFINE INCLUDE DIR (a partir da raiz)
define('INCLUDE_DIR',DIR_PATH."includes/");

define('INCLUDE_JS',DIR_PATH."includes/");

define('TEMPLATES_DIR',SITE_PATH."templates_erp/");

//DEFINE XAJAX DIR
define('XAJAX_DIR',DIR_PATH."includes/xajax");

//DEFINE CSS
define('CSS_FILE',DIR_PATH."classes/classes.css");

//DEFINE IMAGENS DIR (a partir do web root)
define('DIR_IMAGENS',DIR_PATH."imagens/");

//EXEMPLO: /mnt/hd2/ged/

define('DOCUMENTOS_GED',MOUNT_DIR."ged");

define('BOOK_ARQUIVOS',MOUNT_DIR."book_arquivos");

define('ARQUIVO_MORTO',MOUNT_DIR."arquivo_morto");

define('DOCUMENTOS_CHECKIN',MOUNT_DIR."arquivos_checkin");

//define('DOCUMENTOS_SGI',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('qualidade','')));

//define('NORMAS_SGI',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('normas','')));

define('DOCUMENTOS_FINANCEIRO',MOUNT_DIR."financeiro");

define('DOCUMENTOS_PROJETO',MOUNT_DIR."projetos");

//define('DOCUMENTOS_CONTRATOS',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('contratos','')));

//define('DOCUMENTOS_CONTROLE',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('controle','')));

define('DOCUMENTOS_ORCAMENTO',MOUNT_DIR."orcamento");

//define('DOCUMENTOS_MARKETING',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('marketing','_ERP','')));

define('DOCUMENTOS_RH',MOUNT_DIR."rh");

//define('PASTA_DESCRICOES_CARGOS', DOCUMENTOS_RH.'VERIFICAR/002 - NOVO RH/Cargos e salarios/descricao de Cargos/2016');

//define("DOCUMENTOS_FINANCEIRO_TEMP",ROOT_DIR.implode(DIRECTORY_SEPARATOR,array('','financeiro','documentos','')));

//define("COMPROVANTES_PJ",implode(DIRECTORY_SEPARATOR,array('comprovantes_sistema','certidoes_pj','')));

//define("MANUAIS_SISTEMAS",'..'.implode(DIRECTORY_SEPARATOR,array('','manuais_sistemas','documentos',''))); //;"../manuais_sistemas/documentos/");

//define("COMPROVANTES_FECHAMENTO",implode(DIRECTORY_SEPARATOR,array('comprovantes_sistema','fechamento','')));

define("DIRETORIO_VERSOES","_versoes");

define("DIRETORIO_EXCLUIDOS","_excluidos");

define("DIRETORIO_COMENTARIOS","_comentarios");

define("DIRETORIO_DESBLOQUEIOS","_desbloqueios");

define("GRD","-GRD");

define("DISCIPLINAS","DISCIPLINAS");

define("REFERENCIAS","REFERENCIAS");

//define('DOCUMENTOS_CHAMADOS', MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('anexos_chamados','')));

//define("ACOMPANHAMENTO","-ACOMPANHAMENTO");

//define("ACT","-ACT");

define('DIRETORIO_PROJETO', ROOT_DIR);

//define('DOCUMENTOS_BANCO_MATERIAIS', str_replace('\\', '/', DIRETORIO_PROJETO).'/../images/');

//Caminho do projeto

define('IMAGES', ROOT_WEB."/images");

//define('SMARTY_RESOURCE_CHAR_SET', 'ISO-8859-1');

define('SMARTY_RESOURCE_CHAR_SET', 'UTF-8');

define('PROJETO', 'http://'.HOST.'/'.$uri[1]);

define('CRLF',"\r\n");

date_default_timezone_set('America/Sao_Paulo');

session_start();

require_once(INCLUDE_DIR."tools.inc.php"); //OK

require_once(INCLUDE_DIR."conectdb.inc.php"); //OK

require_once(INCLUDE_DIR."include_logs.inc.php"); //OK

$db = new banco_dados();

$logs = new logs();

$array_tables = array();

//VERIFICA SE BANCO DE DADOS EXISTE
$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".DATABASE."';";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
  die ($db->erro);
}

$dados = $db->array_select[0];

//caso não exista o banco de dados, cria
if (empty($dados))
{
	@ini_set(max_execution_time, 300);

	require_once(INCLUDE_DIR."database.inc.php");
	
	require_once(INCLUDE_DIR."database2.inc.php");
	
	require_once(INCLUDE_DIR."database3.inc.php");

	require_once(INCLUDE_DIR."database4.inc.php");

	//require_once(INCLUDE_DIR."database5.inc.php");
	
	@ini_set(max_execution_time, 120);
}

require_once(INCLUDE_DIR."include_email.inc.php");

require_once(INCLUDE_DIR."include_controle.inc.php");

?>