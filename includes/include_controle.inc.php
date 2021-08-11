<?php

	//Configuracoes basicas para controle de acessos e erros
	//criado em 14/09/2017 - Carlos Abreu
	
	//Funcao recursiva para verificacao do sub-modulo para que habilite o botao nas telas anteriores
	function verifica_sub_modulo($id_sub_modulo)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}

		$id_sub_modulo = empty($id_sub_modulo) ? $_SESSION['id_sub_modulo'] : $id_sub_modulo;
		
		$retorno = FALSE;
		
		//Se administrador
		if ($_SESSION["admin"] && $_SESSION["login"]=="admin")
		{
			$retorno = TRUE;
		}
		else
		{
			
			if (empty($db))
			{
				$db = new banco_dados();
			}
			
			$sql = "SELECT permissao FROM ".DATABASE.".permissoes ";
			$sql .= "WHERE permissoes.id_usuario = '".$_SESSION["id_usuario"]."' ";
			$sql .= "AND permissoes.id_sub_modulo = '".$id_sub_modulo."' ";
			$sql .= "AND permissoes.reg_del = 0 ";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				die($db->erro);
			}	
			
			if($db->numero_registros > 0 && intval($db->array_select[0]['permissao']) > 0)
			{
				$retorno = TRUE;
			}
			else
			{
				$sql = "SELECT id_sub_modulo FROM  ".DATABASE.".sub_modulos ";
				$sql .= "WHERE sub_modulos.id_sub_modulo_pai = '".$id_sub_modulo."' ";
				$sql .= "AND sub_modulos.visivel = 1 ";
				$sql .= "AND sub_modulos.reg_del = 0 ";	
				$sql .= "ORDER BY sub_modulos.sub_modulo ";
				
				$db->select($sql,'MYSQL', true);
	
				if($db->erro!='')
				{
					die($db->erro);
				}				
		
				foreach($db->array_select as $regs)
				{
					$retTmp = verifica_sub_modulo($regs["id_sub_modulo"]);
					
					if($retTmp)
					{
						$retorno = TRUE;
						
						break;
					}
					else
					{
						$retorno = FALSE;
					}
				}	
			}
		}
	
		return $retorno;		
	}
	
	function nao_permitido()
	{
		$complemento = !isset($_SESSION['id_funcionario']) ? '?pagina='.$_SERVER['PHP_SELF'] : '';
		$html = '<label class="labels">Acesso Negado, escolha uma das opções a seguir: </label><br /><br />';

		$html .= '<button class="class_botao" onclick="history.back();">Voltar</button> ';
		$html .= '<button class="class_botao" onclick=location.href="../index.php'.$complemento.'";>login</button>';

		echo '
			<html lang="pt-br">
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<meta http-equiv="cache-control" content="max-age=0">
				<meta http-equiv="cache-control" content="no-cache, must-revalidate">
				<meta http-equiv="Expires" content="0">
				
				<title>::.. ERP ..::</title>
				<link href="'.ROOT_WEB.'/classes/classes.css" rel="stylesheet">
				
				<script src="'.ROOT_WEB.'/includes/utils.js"></script>
				</head>
				
				<body>
				<div id="div_tudo" style="position:absolute; left:50%; top:50%; margin-left:-180px; margin-top:-190px;">
					<div class="div_login">
						<div class="header" align="center">
							<img align="middle" src="'.ROOT_WEB.'/imagens/logo_erp.png" width="302" height="70">            
						</div>
						<br />
						<div class="fieldset" align="center">
							'.$html.'
						</div> 
					   </div>
				   </div>
				</body>	
		';    
		exit;
			
	}
	
?>