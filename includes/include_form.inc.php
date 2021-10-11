<?php

	require_once(INCLUDE_DIR."smarty-3.1.39". DIRECTORY_SEPARATOR ."libs". DIRECTORY_SEPARATOR ."Smarty.class.php");
	require_once(INCLUDE_DIR."xajax". DIRECTORY_SEPARATOR ."xajaxExtend.php");		
	
	//Classe com funcoes de configuracao e mensagens	
	class configs
	{	
		//Verifica as permissoes
		//16 - visualiza | 8 - Inclui | 4 - edita | 2 - apaga | 1 - imprime | 0 sem permissao
		function checa_permissao($mascara, &$resposta = '')
		{
			
			$error = FALSE;
		
			$db = new banco_dados();
			
			//Se administrador
			if($_SESSION["admin"])
			{
				$sql = "SELECT id_sub_modulo FROM ".DATABASE.".sub_modulos ";
				$sql .= "WHERE sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
				$sql .= "AND reg_del = 0 ";								
				$status = TRUE;
			}
			else
			{
				$sql = "SELECT permissao FROM ".DATABASE.".permissoes, ".DATABASE.".sub_modulos ";
				$sql .= "WHERE permissoes.id_usuario = '".$_SESSION["id_usuario"]."' ";
				$sql .= "AND permissoes.id_sub_modulo = sub_modulos.id_sub_modulo ";
				$sql .= "AND sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
				$sql .= "AND sub_modulos.visivel = '1' ";
				$sql .= "AND permissoes.reg_del = 0 ";
				$sql .= "AND sub_modulos.reg_del = 0 ";

				$db->select($sql,'MYSQL', true);

				if($db->erro!='')
				{
					if (!empty($resposta))
					{
						$erro = addslashes($db->erro);

						$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 214.';
				
						//MOSTRA TELA COM O COMANDO EXECUTADO CASO ESTEJA EM DEBUG
						if(DEBUG)
						{
							//Apresenta o popup com o alerta
							$resposta->addScript("mdAlert(2,'".$erro."');");
				
							//$resposta->addAssign("modalInfoBody","innerHTML",$erro);
						}
						else 
						{
							$resposta->addScript("mdAlert(2,'".$err_arq."');");
						}
				
						//GRAVA LOG DE ERRO
						if(LOG)
						{
							$texto = "Date: " . date('Y-m-d H:i:s') . " - User: " . $_SESSION["nome_usuario"] . CRLF; 
				
							$texto .= $err_arq . CRLF;
				
							$texto .= $erro . CRLF;
				
							$logs->log_file($texto);
						}
				
						return $resposta;
					}
					
					$status = FALSE;
				}
					
				$regs2 = $db->array_select[0];
				
				if($regs2["permissao"] & $mascara)
				{
					$status = TRUE;
				}
				else
				{
					if (!empty($resposta))
					{
						switch ($mascara)
						{
							case 16: 
								$resposta->addAlert("Sem permissão para visualizar."); //visualizar
							break;
							
							case 8: 
								$resposta->addAlert("Sem permissão para inserir."); //INSERIR
							break;
							
							case 4:
								$resposta->addAlert("Sem permissão para editar."); //EDITAR
							break;
							
							case 2:
								$resposta->addAlert("Sem permissão para excluir."); //EXCLUIR 
							break;
							
							case 1:
								$resposta->addAlert("Sem permissão para imprimir."); //imprimir 
							break;
							
							case 0:
								$resposta->addAlert("Sem permissão de acesso.");  //SEM PERMISAO PARA ACESSO
							break;				
						}
					}
					$status = FALSE;
				}						
			}
			
			return $status;
		}	
	}

	$db = new banco_dados();

	$smarty = new Smarty;
	
	$smarty->template_dir = "templates_erp";
	
	$smarty->compile_dir = DIR_PATH."templates_c";
	
	$smarty->left_delimiter = "<smarty>";
	
	$smarty->right_delimiter = "</smarty>";
	
	$smarty->compile_check = true;
	
	$smarty->force_compile = true;
	
	$smarty->assign('IMAGES', IMAGES);
	
	$smarty->assign('DIR_IMAGENS', DIR_IMAGENS);
		
	$xajax = new xajaxExtend();
	
	//Funcao Xajax de checar sessao
	function checaSessao()
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		$resposta = new xajaxResponse();
		
		if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
		{
			$resposta->addAlert("Usuário não logado no sistema.");
			//$resposta->addAlert($_SESSION["login"]);

		}
	
		return $resposta;
	}
	
	//Funcao Xajax para montar a tela (menus)
	function monta_menu($id_sub_modulo)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		$id_sub_modulo = empty($id_sub_modulo) ? $_SESSION['id_sub_modulo'] : $id_sub_modulo;	
			
		$resposta = new xajaxResponse();
		
		$db = new banco_dados();
			
		$conteudo = '<table border="0" width="100%">';
		
		$sql = "SELECT id_sub_modulo, sub_modulo, caminho_sub_modulo FROM ".DATABASE.".sub_modulos ";
		$sql .= "WHERE sub_modulos.id_sub_modulo_pai = '".$id_sub_modulo."' ";
		$sql .= "AND sub_modulos.visivel = 1 ";
		$sql .= "AND sub_modulos.reg_del = 0 ";
		$sql .= "ORDER BY sub_modulos.sub_modulo ";

		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$erro = addslashes($db->erro);

			$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 471.';
	
			//MOSTRA TELA COM O COMANDO EXECUTADO CASO ESTEJA EM DEBUG
			if(DEBUG)
			{
				//Apresenta o popup com o alerta
				$resposta->addScript("mdAlert(2,'".$erro."');");
	
				//$resposta->addAssign("modalInfoBody","innerHTML",$erro);
			}
			else 
			{
				$resposta->addScript("mdAlert(2,'".$err_arq."');");
			}
	
			//GRAVA LOG DE ERRO
			if(LOG)
			{
				$texto = "Date: " . date('Y-m-d H:i:s') . " - User: " . $_SESSION["nome_usuario"] . CRLF; 
	
				$texto .= $err_arq . CRLF;
	
				$texto .= $erro . CRLF;
	
				$logs->log_file($texto);
			}
	
			return $resposta;
		}
		
		$colunas = 0;
		
		$linhas = TRUE;
		
		foreach($db->array_select as $cont)
		{
			//Se administrador
			if($_SESSION["admin"])
			{
				$habilitado = TRUE;				
			}
			else
			{				
				$habilitado = verifica_sub_modulo($cont["id_sub_modulo"]);				
			}
			
			if($habilitado)
			{
				$enabled = "enabled";
				$class_botao = "class_botao_menu_hab";
			}
			else
			{
				$enabled = "disabled";
				$class_botao = "class_botao_menu_deshab";
			}			
			
			if($linhas)
			{
				$conteudo .= '<tr>';
				$linhas = FALSE;
			}
			
			$conteudo .= '<td class="tabela_body" align="center"><input class="'.$class_botao.'" type="button" name="'.$cont["id_sub_modulo"].'" id="'.$cont["id_sub_modulo"].'" value="'.str_replace(" "," ",$cont["sub_modulo"]).'" onclick=xajax_monta_tela("'.$cont["id_sub_modulo"].','.$cont["caminho_sub_modulo"].'"); '.$enabled.' /></td>';
			
			$colunas++;
			
			if($colunas>=3)
			{
				$conteudo .= '</tr>';
				$linhas = TRUE;
				$colunas = 0;	
			}						
		}
		
		//completa a linha com o total de colunas faltantes
		if($colunas>0)
		{		
			for($i=$colunas;$i<3;$i++)
			{
				$conteudo .= '<td class="tabela_body"></td>';
			}
		}
		
		$conteudo .= '</tr></table>';			
	
		$resposta->addAssign("tela","innerHTML",$conteudo);	
		
		return $resposta;
	}
	
	//Funcao Xajax para montar a janela do modulo
	function monta_tela($id_sub_modulo, $caminho_sub_modulo = '')
	{
		if(!isset($_SESSION))
		{
			session_start();
		}

		$_SESSION["id_sub_modulo"] = $id_sub_modulo;
		
		$_SESSION["caminho_sub_modulo"] = $caminho_sub_modulo;
		
		$include_dir = PAGE_ROOT;
		
		$resposta = new xajaxResponse();

		$db = new banco_dados();
		
		//Se administrador
		if($_SESSION["admin"])
		{
			$sql = "SELECT caminho_sub_modulo, altura, largura, target FROM ".DATABASE.".sub_modulos ";
			$sql .= "WHERE sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
			$sql .= "AND sub_modulos.reg_del = 0 ";		
		}
		else
		{
			$sql = "SELECT caminho_sub_modulo, altura, largura, target FROM ".DATABASE.".sub_modulos ";
			$sql .= "LEFT JOIN ".DATABASE.".permissoes ON (permissoes.id_sub_modulo = sub_modulos.id_sub_modulo AND permissoes.id_usuario = '".$_SESSION["id_usuario"]."' AND permissoes.reg_del = 0) ";			
			$sql .= "WHERE sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
			$sql .= "AND sub_modulos.reg_del = 0 ";
			$sql .= "ORDER BY sub_modulos.sub_modulo ";		
		}
		
		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$erro = addslashes($db->erro);

			$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 607.';
	
			//MOSTRA TELA COM O COMANDO EXECUTADO CASO ESTEJA EM DEBUG
			if(DEBUG)
			{
				//Apresenta o popup com o alerta
				$resposta->addScript("mdAlert(2,'".$erro."');");
	
				//$resposta->addAssign("modalInfoBody","innerHTML",$erro);
			}
			else 
			{
				$resposta->addScript("mdAlert(2,'".$err_arq."');");
			}
	
			//GRAVA LOG DE ERRO
			if(LOG)
			{
				$texto = "Date: " . date('Y-m-d H:i:s') . " - User: " . $_SESSION["nome_usuario"] . CRLF; 
	
				$texto .= $err_arq . CRLF;
	
				$texto .= $erro . CRLF;
	
				$logs->log_file($texto);
			}
	
			return $resposta;
		}
		
		$cont_desp = $db->array_select[0];		
		
		$caminho = $cont_desp["caminho_sub_modulo"];

		switch ($cont_desp["target"])
		{		
			case 1:
				$resposta->addScript("window.open('".$include_dir.'/'.$caminho."');");
			break;
					
			case 2:
				$resposta->addScript("tela('".$include_dir.'/'.$caminho."','".$cont_desp["altura"]."','".$cont_desp["largura"]."');");
			break;
			
			case 3:
				$resposta->addScript("window.open('".$caminho."');");
			break;
			
			default:			
				
				$resposta->addRedirect($include_dir.'/'.$caminho);

		}
	
		return $resposta;
	}
	
	//Funcao Xajax para checar data 
	function checa_data($data, $controle)
	{
	
		$resposta = new xajaxResponse();
		
		$data_array = explode("/", $data);
		
		$dia = $data_array[0];
		$mes = $data_array[1];
		$ano = $data_array[2];
	
		$data_stamp = mktime(0,0,0,$mes, $dia, $ano);
		
		$data_format = getdate($data_stamp);
		
		$dia_semana = $data_format["wday"];
		
		//Se a data informada nao for valida ou o ano for menor/igual a 2005
		if(!checkdate($mes, $dia, $ano) || $ano<=2005)
		{
			$resposta->addAlert("Data inválida.");
			$resposta->addAssign($controle,"value","");
			$resposta->addScript("document.getElementByName('".$controle."')[0].focus();");
		}
	
		return $resposta;
	
	}
	
	$xajax->setCharEncoding("utf-8");
	
	//$xajax->decodeUTF8InputOn();
	
	$page = explode("/",$_SERVER['SCRIPT_FILENAME']);

	$exclusao = array('index.php');	
		
	if(!in_array($page[count($page)-1],$exclusao))
	{
		$xajax->registerPreFunction("checaSessao");
	}
	
	$xajax->registerFunction("monta_menu");
	
	$xajax->registerFunction("monta_tela");
	
	$xajax->registerFunction("checa_data");
?>