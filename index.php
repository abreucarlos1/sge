<?php
/*
		Formulário de Autenticação	
		
		Criado por Carlos Abreu - 20/05/2021
		
		local/Nome do arquivo:
		index.php
	
*/

$user = "";

//seta idioma se não estiver setado
if (!isset($_COOKIE['idioma'])) 
{
   $_COOKIE["idioma"]="1";
   
   setcookie("idioma",1,time()+60*60*24*180);
}

if (isset($_COOKIE['user'])) 
{
   $user = $_COOKIE['user'];
}

require_once("config.inc.php"); //OK

require_once(INCLUDE_DIR."include_form.inc.php"); //OK

$logs->log_acessos(basename(__FILE__));

function autenticacao($dados_form)
{
	$resposta = new xajaxResponse();

	if(isset($_SESSION["id_sub_modulo"]))
	{
		unset($_SESSION["id_sub_modulo"]);
	}
	
	$db = new banco_dados();

	$logs = new logs();

	$registra_adm = false;

	// Recupera o login
	$login = isset($dados_form["login"]) ? addslashes(trim($dados_form["login"])) : FALSE;
	
	// Recupera a senha, a criptografando em MD5
	$senha = isset($dados_form["senha"]) ? $dados_form["senha"] : FALSE;
	
	// Usuário não forneceu a senha ou o login
	if(!$login || !$senha)
	{
		$resposta->addAssign("mensagem","innerHTML","Os campos não podem estar vazios.");
	}
	else
	{

		/**
		* Executa a consulta no banco de dados.
		* Caso o número de linhas retornadas seja 1 o login é válido,
		* caso 0, inválido.
		*/
		$sql = "SELECT data_troca, condicao, senha, perfil, login, usuarios.id_usuario, nivel_atuacao, id_funcionario, id_setor_aso, nome, status FROM ".DATABASE.".usuarios ";
		$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (usuarios.id_usuario = funcionarios.id_usuario AND funcionarios.reg_del = 0 ) ";
		$sql .= "WHERE usuarios.login = '" . $login . "' ";
		$sql .= "AND usuarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
				$erro = addslashes($db->erro . $sql);

				$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 67.';

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

		$num_regs = $db->numero_registros;
		
		$dados = $db->array_select[0];
		
		if(LOG_ACOES[0])
		{
			$logs->log_acoes($dados["id_usuario"],$dados["id_usuario"],'LOGIN',$sql,'autenticacao',basename(__FILE__));
		}

		//$resposta->addAlert($num_regs);

		// Caso o usuário tenha digitado um login inválido.
		if($num_regs<=0)
		{
			//$resposta->addAssign("mensagem","innerHTML","Login/Senha inválida1");

			//return $resposta;

			$pass_adm = gerar_hash(SENHA_ADM, 'admin'); //senha/nome

			$pass_administrador = gerar_hash(SENHA_ADM, 'administrador');
		}

		//verifica se administrador
		if(($login == 'administrador' || $login == 'admin'))
		{
			if($dados["senha"]!='')			
			{
				$pass_adm = $dados["senha"];

				$pass_administrador = $dados["senha"];
			}
			else 
			{
				$registra_adm = true;
			}

			// Agora verifica a senha
			if(!(valida_pw($senha,$login,$pass_adm) || valida_pw($senha,$login,$pass_administrador)))
			{

				$resposta->addAssign("mensagem","innerHTML",'Login/Senha inválida.');
						
				return $resposta;
			}

			if(LOG_ACOES[0])
			{
				$logs->log_acoes('0','0','LOGIN','ADMINISTRADOR','autenticacao',basename(__FILE__));
			}

			if($registra_adm)
			{
				$resposta->addScript("troca_senha('administrador','1',true);");
				
				return $resposta;	
			}

			$_SESSION["admin"] = TRUE;

			$_SESSION["perfil"] = 1;
			
			$_SESSION["login"] = $login;
			
			$_SESSION["nome_usuario"] = stripslashes("ADMINISTRADOR DO SISTEMA");			
			
			$_SESSION["id_usuario"] = $dados["id_usuario"];

			$_SESSION["id_funcionario"] = 0;
			
			if($dados_form["pagina"]!="")
			{
				$resposta->addRedirect($dados_form["pagina"]);
			}
			else
			{
				$resposta->addRedirect("inicio.php");
			}		
		}
		else
		{
			$_SESSION["admin"] = FALSE;
			
			$data_referencia = date("d/m/Y");
			
			$data_troca = mysql_php($dados["data_troca"]);
			
			$dias_restantes = (DIAS_LIMITE) - dif_datas($data_referencia,$data_troca);
	
			if($dados["condicao"] == 0) //1-ativo / 0-inativo
			{
				$resposta->addAssign("mensagem","innerHTML", 'Usuário Inativo');
			}
			else
			{
				// Caso o usuário tenha digitado um login válido o número de linhas será 1..
				if($num_regs>=1)
				{	
					// Obtém os dados do usuário, para poder verificar a senha e passar os demais dados para a sessão									
					// Agora verifica a senha
					//if(!strcmp($senha, $enc->decrypt($dados["senha"])))
					if(valida_pw($senha,$login,$dados["senha"]))
					{
						if($dados['perfil']==1)
						{
							$_SESSION["admin"] = TRUE;
						}						
						
						// TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário
						$_SESSION["login"] = $dados["login"];
						
						$_SESSION["nivel_atuacao"] = $dados["nivel_atuacao"];
						
						$_SESSION["id_usuario"] = $dados["id_usuario"];
						
						$_SESSION["id_funcionario"] = $dados["id_funcionario"];
						
						$_SESSION["perfil"] = $dados["perfil"];
						
						$_SESSION["id_setor_aso"] = $dados["id_setor_aso"];
						
						$_SESSION["nome_usuario"] = stripslashes($dados["nome"]);
						
						if($dias_restantes <= 0)
						{
							$resposta->addScript("troca_senha('".$dados["login"]."','".$dados["id_usuario"]."');");
							
							return $resposta;	
						}
						
						//se faltar 10 dias para o vencimento, mostra mensagem
						if($dias_restantes<=10)
						{
							//$resposta->addAlert("Sua senha irá expirar em ".abs($dias_restantes)." dias.");
							
							$resposta->addScript("mdAlert(1,'Sua senha irá expirar em '".abs($dias_restantes)."' dias.','SENHA EXPIRADA');");
						}						
						
						if($dados["status"]=="0")
						{
							if($dados_form["pagina"]!="")
							{
								$resposta->addRedirect($dados_form["pagina"]);
							}
							else
							{
								$resposta->addRedirect("inicio.php");
							}							
						}
						else
						{
							$resposta->addScript("troca_senha('".$dados["login"]."','".$dados["id_usuario"]."');");
						}						
					}
					// Senha inválida
					else
					{
						$resposta->addAssign("mensagem","innerHTML","Login/Senha inválida.");
						
						return $resposta;
					}
				}
				// login inválido
				else
				{
					$resposta->addAssign("mensagem","innerHTML","Login/Senha inválida");
				}
			}			
		}
	}
	
	return $resposta;
}

//altera senha de acesso
function enviar($dados_form)
{
	$resposta = new xajaxResponse();	

	if($dados_form["login"]!="" && $dados_form["senha"]!="")
	{	
		$db = new banco_dados();

		$logs = new logs();
	
		$sql = "SELECT id_usuario, login, email FROM ".DATABASE.".usuarios ";
		$sql .= "WHERE usuarios.reg_del = 0 ";
		$sql .= "AND usuarios.login = '". minusculas(trim($dados_form["login"])). "' ";
		//$sql .= "AND usuarios.email = '".minusculas(trim($dados_form["email"]))."' ";		

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$erro = addslashes($db->erro . $sql);

			$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 249.';

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
		
		$reg = $db->array_select[0];

		// Se o número de registros for maior que zero, então existe o registro...
		if ($db->numero_registros>0)
		{
			$senha = gerar_hash(trim($dados_form["senha"]), minusculas(trim($dados_form["login"])));
								
			$usql = "UPDATE ".DATABASE.".usuarios SET ";
			$usql .= "senha = '". $senha . "', ";
			$usql .= "status = '0', ";
			$usql .= "data_troca = '".date("Y-m-d")."' ";
			$usql .= "WHERE id_usuario = '".$reg["id_usuario"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$erro = addslashes($db->erro . $usql);

				$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 297.';

				//MOSTRA TELA COM O COMANDO EXECUTADO CASO ESTEJA EM DEBUG
				if(DEBUG)
				{
					//Apresenta o popup com o alerta
					$resposta->addScript("mdAlert(2,'".$erro."');");

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

			if(LOG_ACOES[0])
			{
				$logs->log_acoes($reg["id_usuario"],$reg["id_usuario"],'ALTERACAO',$usql,'enviar',basename(__FILE__));
			}

			//$resposta->addScript("mdClose();");

			$mensagem = "Seus dados para acesso são:<br><br>";
			$mensagem .= "login: " . $reg["login"] . "<br>";
			$mensagem .= "Senha: " . trim($dados_form["senha"]) . "<br><br>";
			$mensagem .= "Tecnologia da Informação <br><br>";
			$mensagem .= "Caso tenha recebido este e-mail sem sua solicitação, favor desconsiderá-lo. <br><br>";
			$mensagem .= "O envio desta confirmação foi registrado em nosso banco de dados em ". date("d/m/Y") . " as " . date("H:i") . " <br><br><br>";
			$mensagem .= "E-mail enviado em ". date("d/m/Y") . " as " . date("H:i") . " <br>"; 

			if(ENVIA_EMAIL)
			{
				$params = array();
			
				$params['from']	= "empresa@".DOMINIO;
				
				$params['from_name'] = "RECUPERAÇÃO DE SENHA - EMPRESA X";
				
				$params['subject'] = "RECUPERAÇÃO DE SENHA";
				
				$params['emails']['to'][] = array('email' => $reg["email"], 'nome' => $reg["login"]);
				
				$mail = new email($params);
				
				$mail->montaCorpoEmail($mensagem);
		
				if(!$mail->Send())
				{
					//$resposta->addAlert("Erro email: ".$mail->ErrorInfo);
					$resposta->addScript("mdAlert(2,'Erro no envio do e-mail.','ERRO E-MAIL');");
				}
				else
				{
					//$resposta->addAlert("Enviado com sucesso.");
					$resposta->addScript("mdAlert(1,'Enviado com sucesso.','ENVIO E-MAIL');");
				}
				
				$mail->ClearAddresses();
			}
			else
			{
				//$resposta->addScriptCall('modal', $mensagem, '300_650', 'Conteúdo email', 2);
				$resposta->addScript("mdAlert(3,'".$mensagem."','CONTEÚDO E-MAIL');");
			}
			
		}
		else
		{
			//verifica se é adm
			if($dados_form["adm"])
			{
				$senha = gerar_hash(trim($dados_form["senha"]), 'admin');

				$isql = "INSERT INTO ".DATABASE.".usuarios (nome, login, senha, status, data_troca, perfil) VALUES (";
				$isql .= "'Administrador', ";
				$isql .= "'admin', ";
				$isql .= "'".$senha."', ";
				$isql .= "'0', ";
				$isql .= "'".date("Y-m-d")."',";
				$isql .= "'1')";

				$db->insert($isql,'MYSQL');
			
				if($db->erro!='')
				{
					$erro = addslashes($db->erro . $isql);
	
					$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 441.';
			
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
				
				if(LOG_ACOES[0])
				{
					$logs->log_acoes(1,$db->insert_id,'INCLUSAO',$isql,'enviar',basename(__FILE__));
				}

				$senha = gerar_hash(trim($dados_form["senha"]), 'administrador');

				$isql = "INSERT INTO ".DATABASE.".usuarios (nome, login, senha, status, data_troca, perfil) VALUES (";
				$isql .= "'Administrador', ";
				$isql .= "'administrador', ";
				$isql .= "'".$senha."', ";
				$isql .= "'0', ";
				$isql .= "'".date("Y-m-d")."',";
				$isql .= "'1')";

				$db->insert($isql,'MYSQL');
			
				if($db->erro!='')
				{
					$erro = addslashes($db->erro . $isql);
	
					$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 490.';
			
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
				
				if(LOG_ACOES[0])
				{
					$logs->log_acoes(1,$db->insert_id,'INCLUSAO',$isql,'enviar',basename(__FILE__));
				}

				$resposta->addRedirect("index.php");
			}
			else
			{
				//$resposta->addAlert("Usuário inexistente.");
				$resposta->addScript("mdAlert(2,'Usuário inexistente.','USUÁRIO');");
			}		
		}
	}
	else
	{
		//$resposta->addAlert("Os campos devem estar preenchidos");
		$resposta->addScript("mdAlert(2,'Os campos devem estar preenchidos.', 'ALTERAR SENHA');");
	}

	return $resposta;
}

function validar_senha($dados_form)
{
	$resposta = new xajaxResponse();

	if(($dados_form["senha"]!=$dados_form["confsenha"]) || ($dados_form["senha"]==''))
	{
		$resposta->addAssign("mensagem","innerHTML","Senhas diferentes.");
		$resposta->addAssign("senha","value","");
		$resposta->addAssign("confsenha","value","");
		$resposta->addScript('document.getElementsByName("senha")[0].focus();');
	}

	return $resposta;
}

function atualiza($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();

	$logs = new logs();
	
	if($dados_form["senha"]=="")
	{
		//$resposta->addAlert("A senha não poderá ser vazia.");
		$resposta->addScript("mdAlert(2,'A senha não poderá ser vazia.', 'ALTERAR SENHA');");
	}
	else
	{

		$sql = "SELECT login, senha FROM ".DATABASE.".usuarios ";
		$sql .="WHERE id_usuario = '".$dados_form["id_usuario"]."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$erro = addslashes($db->erro . $sql);

			$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 588.';

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
		
		if($db->numero_registros>0)
		{
			
			$regs = $db->array_select[0];
							
			if(trim($dados_form["senha"])=="12345")
			{
				//$resposta->addAlert("Esta senha não pode ser utilizada.");
				$resposta->addScript("mdAlert(1,'Esta senha não poderá ser utilizada.', 'ALTERAR SENHA');");

				return $resposta;				
			}
			else
			{			
				if(valida_pw(trim($dados_form["senha"]),trim($regs["login"]),$regs["senha"]))		
				{
					//$resposta->addAlert("As senhas devem ser diferentes.");
					$resposta->addScript("mdAlert(1,'As senhas devem ser diferentes.', 'ALTERAR SENHA');");

					return $resposta;
				}
				else
				{					
					$test = password_check_complex($dados_form["senha"]);
					
					if(!$test)
					{
						//$resposta->addAlert('Senha dever ter no mínimo:'.chr(13).TAMANHO_SENHA.' caracteres;'.chr(13).'1 caracter maiúsculo;'.chr(13).'1 caracter minúsculo;'.chr(13).'1 número;'.chr(13).'1 símbolo ex: (!@#$%)');
						$resposta->addScript("mdAlert(1,''Senha dever ter no mínimo:'.chr(13).TAMANHO_SENHA.' caracteres;'.chr(13).'1 caracter maiúsculo;'.chr(13).'1 caracter minúsculo;'.chr(13).'1 número;'.chr(13).'1 símbolo ex: (!@#$%)', 'ALTERAR SENHA');");

						$resposta->addAssign("senha","value","");
						$resposta->addAssign("confsenha","value","");
						$resposta->addScript('document.getElementsByName("senha")[0].focus();');
					}
					else
					{					
						//$senha = $enc->encrypt(trim($dados_form["senha"]));
						$senha = gerar_hash(trim($dados_form["senha"]),trim($regs["login"]));
								
						$usql = "UPDATE ".DATABASE.".usuarios SET ";
						$usql .= "senha = '". $senha . "', ";
						$usql .= "status = '0', ";
						$usql .= "data_troca = '".date("Y-m-d")."' ";
						$usql .= "WHERE id_usuario = '".$_SESSION["id_usuario"]."' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$erro = addslashes($db->erro . $usql);

							$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 667.';
			
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

						if(LOG_ACOES[0])
						{
							$logs->log_acoes($_SESSION["id_usuario"],$_SESSION["id_usuario"],'ALTERACAO',$usql,'atualiza',basename(__FILE__));
						}
						
						//$resposta->addAlert("Senha alterada com sucesso.");
						$resposta->addScript("mdAlert(1,'Senha alterada com sucesso.');");
					}
				}
			}		
		}
		else
		{
			//$resposta->addAlert("Usuário não existe.");
			$resposta->addScript("mdAlert(2,'Usuário inexistente.');");
		}
		
		//$resposta->addScript('window.close();');		
	}
	
	return $resposta;
}

$xajax->registerFunction("autenticacao");
$xajax->registerFunction("enviar");
$xajax->registerFunction("validar_senha");
$xajax->registerFunction("atualiza");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","");

$smarty->assign("nome_empresa",NOME_EMPRESA);

$smarty->assign("nome_sistema",NOME_SISTEMA . '-');

$smarty->assign("nome_formulario","LOGIN");

$smarty->assign("revisao_documento","V0");

$smarty->assign("pagina",isset($_GET["pagina"]) ? $_GET["pagina"] : null);

$smarty->assign("user",$user);

$smarty->assign("classe",CSS_FILE);

$smarty->display("index.tpl");

?>

<script>

function limpa_div(div)
{
	div = document.getElementById(div);
	div.innerHTML = '';
}

function esqueceusenha()
{
	//var dir_imagens = '/imagens/';
	var conteudo = '';
	var botoes = '';

	//conteudo = '<form name="frm_pass" id="frm_pass" method="POST">';		  
	conteudo += '<label for="nome" class="labels">Usuário</label><br />';
	conteudo += '<input name="nome" id="nome" type="text" placeholder="Usuário" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<label for="email" class="labels">E-mail</label><br />';
	conteudo += '<input name="email" id="email" type="text" placeholder="E-mail" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	conteudo += '<label for="senha" class="labels">Nova senha</label><br />';
	conteudo += '<input name="senha" id="senha" type="password" placeholder="Senha" class="caixa" style="text-transform:none;" value="" size="50"/><br />';
	//conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_enviar(xajax.getFormValues("frm_pass")); value="Enviar" />&nbsp';
	//conteudo += '<input name="button" type="button" class="class_botao" onclick=divPopupInst.destroi(1); style="cursor:pointer;" value="Fechar" />';
	//conteudo += '</form>';

	botoes = '<button type="button" class="btn btn-outline-primary btn-sm" onclick=xajax_enviar(xajax.getFormValues("frm_modal"));>Enviar</button>';
	//botoes += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>';
	
	
	//modal(conteudo, 'p', 'ESQUECI MINHA SENHA',1,dir_imagens);


	mdAlert(3, conteudo, 'ESQUECI MINHA SENHA', botoes);
	
	return true;
}

function troca_senha(login,id_usuario,adm=false)
{
	//var dir_imagens = '<?php echo '/'. BASEDIR . '/imagens/'; ?>';

	var conteudo = '';
	var botoes = '';

	//conteudo = '<form name="frm_pass" id="frm_pass" method="POST">';
	conteudo += '<label for="login" class="labels">login</label><br />';
    conteudo += '<input name="login" id="login" type="text" class="caixa" readonly="readonly" value="'+login+'" size="50"/><br /> ';
    conteudo += '<input name="id_usuario" id="id_usuario" type="hidden"  value="'+id_usuario+'"/>';
	conteudo += '<input name="adm" id="adm" type="hidden"  value="'+adm+'"/>';
	conteudo += '<label for="senha" class="labels">Senha</label><br />';
    conteudo += '<input name="senha" type="password" placeholder="Senha" class="caixa" style="text-transform:none;" id="senha" onKeyPress=limpa_div("mensagem"); size="30" /><br >';
	conteudo += '<label for="confsenha" class="labels">Confirme a senha</label><br />';
    conteudo += '<input name="confsenha" type="password" placeholder="Confime a senha" class="caixa" style="text-transform:none;" id="confsenha" size="30" onblur=xajax_validar_senha(xajax.getFormValues("frm_modal")); /><br />';
	conteudo += '<div class="alerta_erro" id="mensagem"> </div><br />';
	//conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_atualiza(xajax.getFormValues("frm_pass")); value="Alterar" />&nbsp';
	//conteudo += '<input name="button" type="button" class="class_botao" onclick=divPopupInst.destroi(1); style="cursor:pointer;" value="Fechar" />';
	//conteudo += '</form>';
	botoes = '<button type="button" class="btn btn-primary" onclick=xajax_enviar(xajax.getFormValues("frm_modal"));>Alterar</button>';
	//botoes += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>';

	//modal(conteudo, 'p', 'TROCAR SENHA',1,dir_imagens);
	mdAlert(3, conteudo, 'ALTERAR SENHA', botoes);
	
	return true;	
}

</script>