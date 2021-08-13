<?php
/*
		Formulário Inicial	
		
		Criado por Carlos Abreu - 20/05/2021
		
		local/Nome do arquivo:
		inicio.php
		
*/

require_once("config.inc.php"); //ok

require_once(INCLUDE_DIR."include_form.inc.php"); //ok

$logs->log_acessos(basename(__FILE__));

setcookie("user",$_SESSION["login"],time()+60*60*24*180);

//MONTA A TELA DINAMICAMENTE COM REGISTROS DO BANCO DE DADOS
function tela()
{
	if(!isset($_SESSION))
	{
		session_start();
	}

	$resposta = new xajaxResponse();	

	$qtd_botoes = 3;
	
	$db = new banco_dados();

	$logs = new logs();
	
    /*
	$sql = "SELECT id_modulo, modulo FROM ".DATABASE.".modulos ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "ORDER BY ordem ";

	$db->select($sql,'MYSQL', true);
	
	$regs = $db->array_select;

	if($db->erro!='')
	{
		$erro = addslashes($db->erro . $sql);

		$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 34.';

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

	$conteudo = '';	
    
	foreach($regs as $cont_desp)
	{
		if (!is_array($cont_desp))
		{
			continue;
		}
				
		$conteudo .= '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
		$conteudo .= '<tr valign="center">';
		//$conteudo .= '<td align="center"><img src="'.DIR_IMAGENS.'tag_'.minusculas($cont_desp["modulo"]).'.png"></td>';
		$conteudo .= '<td align="center">&nbsp</td>';
		$conteudo .= '<td>';		
		$conteudo .= '<table border="0" width="100%" cellspacing="1px" cellpadding="1px">';
		
		$sql = "SELECT id_sub_modulo, sub_modulo FROM ".DATABASE.".sub_modulos ";
		$sql .= "WHERE sub_modulos.id_modulo = '".$cont_desp["id_modulo"]."' ";
		$sql .= "AND sub_modulos.reg_del = 0 ";
		$sql .= "AND sub_modulos.id_sub_modulo_pai = 0 ";
		$sql .= "AND sub_modulos.visivel = 1 ";
		$sql .= "ORDER BY sub_modulos.sub_modulo ";

		$array_sub = $db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$erro = addslashes($db->erro . $sql);

			$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 92.';

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
		
		foreach($array_sub as $cont)
		{
			if (!is_array($cont))
			{
				continue;
			}
				
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
			
			$conteudo .= '<td class="tabela_body" align="center"><input class="'.$class_botao.'" type="button" name="'.$cont["id_sub_modulo"].'" id="'.$cont["id_sub_modulo"].'" value="'.str_replace(" "," ",$cont["sub_modulo"]).'" onclick=xajax_monta_tela("'.$cont["id_sub_modulo"].'"); '.$enabled.' /></td>';
			
			$colunas++;
			
			if($colunas>=$qtd_botoes)
			{
				$conteudo .= '</tr>';
				$linhas = TRUE;
				$colunas = 0;	
			}
		}
		
		//completa a linha com o total de colunas faltantes
		if($colunas>0)
		{		
			for($i=$colunas;$i<$qtd_botoes;$i++)
			{
				$conteudo .= '<td class="tabela_body"><input class="class_botao_menu_deshab" type="button" name="btn_'.rand($i).'" id="btn_'.rand($i).'" value=" " disabled /></td>';
			}
		}
		
		$conteudo .= '</tr></table>';
		
		$conteudo .= '</td>';
		$conteudo .= '</tr>';
		$conteudo .= '</table>';	
	}
    */

    $colunas = 0;

    $linhas = TRUE;
    
    $sql = "SELECT modulos.id_modulo, modulos.modulo, id_sub_modulo, sub_modulo FROM ".DATABASE.".modulos, ".DATABASE.".sub_modulos ";
    $sql .= "WHERE modulos.reg_del = 0 ";
    $sql .= "AND sub_modulos.reg_del = 0 ";
    $sql .= "AND sub_modulos.id_modulo = modulos.id_modulo ";
    $sql .= "AND sub_modulos.reg_del = 0 ";
    $sql .= "AND sub_modulos.id_sub_modulo_pai = 0 ";
    $sql .= "AND sub_modulos.visivel = 1 ";
    $sql .= "ORDER BY modulos.ordem, sub_modulos.sub_modulo ";

    $array_sub = $db->select($sql,'MYSQL', true);

    if($db->erro!='')
    {
        $erro = addslashes($db->erro . $sql);

        $err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 205.';

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

    foreach($array_sub as $cont)
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
            //$class_botao = "class_botao_menu_hab";
        }
        else
        {
            $enabled = "disabled";
            //$class_botao = "class_botao_menu_deshab";
        }

        if($linhas)
        {
            $conteudo .= '<div class="row">';

            $linhas = FALSE;
        }        

        $conteudo .= '<div class="col">';

            $conteudo .= '<button class="btn btn-outline-primary btn-sm btn-block" type="button" name="'.$cont["id_sub_modulo"].'" id="'.$cont["id_sub_modulo"].'" onclick=xajax_monta_tela("'.$cont["id_sub_modulo"].'"); '.$enabled.'>' . $cont["sub_modulo"] . '</button>';
        
        $conteudo .= '</div>';
        
        $colunas++;

        if($colunas>=$qtd_botoes)
        {
            $colunas = 0;
            $conteudo .= '</div>';
            $linhas = TRUE;	
        }

		//completa a linha com o total de colunas faltantes
        /*
		if($colunas>0)
		{		
			for($i=$colunas;$i<$qtd_botoes;$i++)
			{
                $conteudo .= '<div class="col">';

				    $conteudo .= '<button class="btn btn-outline-primary" type="button" name="btn_'.rand($i).'" id="btn_'.rand($i).'" disabled />&nbsp;</button>';
                
                $conteudo .= '</div>';
            }
		}
        */
           
    }

	$resposta->addAssign("frame_inicio","innerHTML",$conteudo);
	
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
		$resposta->addScript("mdAlert(1,'A senha não poderá ser vazia.');");
	}
	else
	{
		$sql = "SELECT id_usuario, login, senha FROM ".DATABASE.".usuarios ";
		$sql .="WHERE id_usuario = '".$dados_form["id_usuario"]."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$erro = addslashes($db->erro . $sql);

			$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 234.';

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
				$resposta->addAlert("Esta senha não pode ser utilizada.");
			}
			else
			{			
				if(valida_pw(trim($dados_form["senha"]),trim($regs["login"]),$regs["senha"]))		
				{
					$resposta->addAlert("As senhas devem ser diferentes.");
				}
				else
				{					
					$test = password_check_complex($dados_form["senha"]);
					
					if(!$test)
					{
						$resposta->addAlert('Senha dever ter no mínimo:'.chr(13).TAMANHO_SENHA.' caracteres;'.chr(13).'1 caracter maiúsculo;'.chr(13).'1 caracter minúsculo;'.chr(13).'1 número;'.chr(13).'1 símbolo ex: (!@#$%)');
						$resposta->addAssign("senha","value","");
						$resposta->addAssign("confsenha","value","");
						$resposta->addScript('document.getElementsByName("senha")[0].focus();');
					}
					else
					{					
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

							$err_arq = 'Erro no arquivo ' . basename(__FILE__) . ', linha 305.';
			
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
						
						$resposta->addAlert("Senha alterada com sucesso.");
					}
				}
			}		
		}
		else
		{
			$resposta->addAlert("Usuário não existe.");
		}
		
		$resposta->addScript('window.close();');		
	}
	
	return $resposta;
}

$xajax->registerFunction("tela");
$xajax->registerFunction("validar_senha");
$xajax->registerFunction("atualiza");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("nome_empresa",NOME_EMPRESA);

$smarty->assign("nome_sistema",NOME_SISTEMA . '-');

$smarty->assign("nome_formulario","MENU INICIAL");

$smarty->assign("revisao_documento","V0");

$smarty->assign("body_onload","xajax_tela();");

/*
$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);
*/

$smarty->display("inicio.tpl");

?>

<script>

function abrejanela(nome,caminho,largura,altura)
{
  params = "width="+largura+",height="+altura+",resizable=0,status=0,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
  windows = window.open( caminho, nome , params);
  
  if(window.focus) 
  {
	setTimeout("windows.focus()",100);
  }  
}


function troca_senha(login,id_usuario)
{
	var conteudo = '';
	var botoes = '';
	//var dir_imagens = '<?php echo '/'. BASEDIR . '/imagens/'; ?>';
	
	//conteudo = '<form name="frm_pass" id="frm_pass" method="POST">';
	conteudo += '<label for="login" class="labels">login</label><br />';
    conteudo += '<input name="login" id="login" type="text" class="caixa" value="'+login+'" readonly="readonly" size="50"/><br /> ';
    conteudo += '<input name="id_usuario" id="id_usuario" type="hidden"  value="'+id_usuario+'"/>';
	conteudo += '<label for="senha" class="labels">Senha</label><br />';
    conteudo += '<input name="senha" type="password" class="caixa" id="senha" onKeyPress=limpa_div("mensagem"); size="30" /><br >';
	conteudo += '<label for="confsenha" class="labels">Confirme a senha</label><br />';
    conteudo += '<input name="confsenha" type="password" class="caixa" id="confsenha" size="30" onblur=xajax_validar_senha(xajax.getFormValues("frm_pass")); /><br />';
	conteudo += '<div class="alerta_erro" id="mensagem"> </div><br />';
	//conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_atualiza(xajax.getFormValues("frm_pass")); value="Alterar" />&nbsp';
	//conteudo += '<input name="button" type="button" class="class_botao" onclick=divPopupInst.destroi(1); style="cursor:pointer;" value="Fechar" />';
	//conteudo += '</form>';
	botoes = '<button type="button" class="btn btn-primary" onclick=xajax_atualiza(xajax.getFormValues("frm_modal"));>Alterar</button>';
	//botoes += '<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>';

	//modal(conteudo, 'p', 'TROCAR SENHA',1, dir_imagens);

	mdAlert(3, conteudo, 'ALTERAR SENHA', botoes);

	return true;	
}
	
</script>