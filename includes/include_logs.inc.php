<?php

	//Classe com funcoes de log de erros	
	class logs
	{
        //Registra os erros em arquivo
		function log_file($texto)
		{
			$dir = SITE_PATH . DIRECTORY_SEPARATOR . "logs";

            $nome_arquivo = "log_sistema.log";

			//Se ainda não existir a pasta, cria
			if(!is_dir($dir))
			{
				if(!mkdir($dir,0755,TRUE))
				{
					die("Erro ao criar o diretório. " . $dir);	
				}
			}

            //VERIFICA O TAMANHO DO ARQUIVO DE LOG
            if(file_exists($dir . DIRECTORY_SEPARATOR . $nome_arquivo))
            {
                $size = filesize($dir . DIRECTORY_SEPARATOR . $nome_arquivo);
                //PERCORRE OS SUFIXOS DE ARQUIVOS DE LOG PARA INCREMENTAR O SUFIXO DE BACKUP
                for($i=1;$i<=999;$i++)
                {
                    if(file_exists($dir . DIRECTORY_SEPARATOR . $nome_arquivo . "." . sprintf("%03d",$i)))
                    {
                        $num_atual = sprintf("%03d",$i + 1);
                        break;
                    }
                }
                //NÃO ACHOU O ARQUIVO
                if(empty($num_atual))
                {
                    $num_atual = sprintf("%03d",1);
                }
                //SE MAIOR QUE O TAMANHO DEFINIDO, RENOMEIA O ARQUIVO PRINCIPAL PARA BACKUP
                if($size >= LOG_SIZE)
                {
                    if(!rename($dir . DIRECTORY_SEPARATOR . $nome_arquivo, $dir . DIRECTORY_SEPARATOR . $nome_arquivo . "." . $num_atual))
                    {
                        die("Erro ao renomear o arquivo.");
                    }
                }
            }
            
            $handle = fopen($dir . DIRECTORY_SEPARATOR . $nome_arquivo , "a");

            if (is_writable($dir . DIRECTORY_SEPARATOR . $nome_arquivo)) 
            {
                $texto .= CRLF;

                // Em nosso exemplo, nós vamos abrir o arquivo $filename
                // em modo de adição. O ponteiro do arquivo estará no final
                // do arquivo, e é pra lá que $conteudo irá quando o 
                // escrevermos com fwrite().
               
                // Escreve $conteudo no nosso arquivo aberto.
                if (fwrite($handle, $texto) === FALSE)
                {
                    die("Não foi possível escrever no arquivo ". $dir . DIRECTORY_SEPARATOR . $nome_arquivo);
                }
               
                fclose($handle);               
            } 
            else 
            {
               die("O arquivo ".$dir . DIRECTORY_SEPARATOR . $nome_arquivo . " não pode ser criado/alterado");
            }
		}

        //Registra as acoes para fins de auditoria
		function log_acoes($id_usuario,$id_registro,$acao,$query,$funcao,$arquivo)
		{
			$dir = SITE_PATH . DIRECTORY_SEPARATOR . "logs";

            $nome_arquivo = "log_acoes.log";

			//Se ainda não existir a pasta, cria
			if(!is_dir($dir))
			{
				if(!mkdir($dir,0755,TRUE))
				{
					die("Erro ao criar o diretório. " . $dir);	
				}
			}

            //VERIFICA O TAMANHO DO ARQUIVO DE LOG
            if(file_exists($dir . DIRECTORY_SEPARATOR . $nome_arquivo))
            {
                $size = filesize($dir . DIRECTORY_SEPARATOR . $nome_arquivo);

                //PERCORRE OS SUFIXOS DE ARQUIVOS DE LOG PARA INCREMENTAR O SUFIXO DE BACKUP
                for($i=1;$i<=999;$i++)
                {
                    if(file_exists($dir . DIRECTORY_SEPARATOR . $nome_arquivo . "." . sprintf("%03d",$i)))
                    {
                        $num_atual = sprintf("%03d",$i + 1);
                        break;
                    }
                }
                //NÃO ACHOU O ARQUIVO
                if(empty($num_atual))
                {
                    $num_atual = sprintf("%03d",1);
                }
                //SE MAIOR QUE O TAMANHO DEFINIDO, RENOMEIA O ARQUIVO PRINCIPAL PARA BACKUP
                if($size >= LOG_SIZE)
                {
                    if(!rename($dir . DIRECTORY_SEPARATOR . $nome_arquivo, $dir . DIRECTORY_SEPARATOR . $nome_arquivo . "." . $num_atual))
                    {
                        die("Erro ao renomear o arquivo.");
                    }
                }
            }

            $handle = fopen($dir . DIRECTORY_SEPARATOR . $nome_arquivo , "a");

            if (is_writable($dir . DIRECTORY_SEPARATOR . $nome_arquivo)) 
            {
                $texto = '';
                
                if(in_array('I',LOG_ACOES) && $acao == 'INCLUSAO')
                {
                    $texto .= 'INCLUSAO - Usuário: ' .$id_usuario. ' ,Data: ' . date('Y-m-d H:i:s') . ', Nº registro: ' . $id_registro . CRLF;
                    $texto .= 'Arquivo: ' . $arquivo . ' ,Função: ' . $funcao . CRLF;
                    $texto .= 'Query executada: ' . $query . CRLF;
                }

                if(in_array('A',LOG_ACOES) && $acao == 'ALTERACAO')
                {
                    $texto .= 'ALTERACAO - Usuário: ' .$id_usuario. ' ,Data: ' . date('Y-m-d H:i:s') . ', Nº registro: ' . $id_registro . CRLF;
                    $texto .= 'Arquivo: ' . $arquivo . ' ,Função: ' . $funcao . CRLF;
                    $texto .= 'Query executada: ' . $query . CRLF;
                }

                if(in_array('E',LOG_ACOES) && $acao == 'EXCLUSAO')
                {
                    $texto .= 'EXCLUSAO - Usuário: ' .$id_usuario. ' ,Data: ' . date('Y-m-d H:i:s') . ', Nº registro: ' . $id_registro . CRLF;
                    $texto .= 'Arquivo: ' . $arquivo . ' ,Função: ' . $funcao . CRLF;
                    $texto .= 'Query executada: ' . $query . CRLF;
                }

                if(in_array('L',LOG_ACOES) && $acao == 'LOGIN')
                {
                    $texto .= 'LOGIN - Usuário: ' .$id_usuario. ' ,Data: ' . date('Y-m-d H:i:s') . CRLF;
                    $texto .= 'Arquivo: ' . $arquivo . ' ,Função: ' . $funcao . CRLF;
                }

                // Em nosso exemplo, nós vamos abrir o arquivo $filename
                // em modo de adição. O ponteiro do arquivo estará no final
                // do arquivo, e é pra lá que $conteudo irá quando o 
                // escrevermos com fwrite().               
                // Escreve $conteudo no nosso arquivo aberto.
                if (fwrite($handle, $texto) === FALSE)
                {
                    die("Não foi possível escrever no arquivo ". $dir . DIRECTORY_SEPARATOR . $nome_arquivo);
                }
               
                fclose($handle);               
            } 
            else 
            {
               die("O arquivo ".$dir . DIRECTORY_SEPARATOR . $nome_arquivo . " não pode ser criado/alterado");
            }
		}

        //Registra o acesso as paginas
        function log_acessos($arquivo)
        {
            if(LOG_ACESSOS)
            {
                $dir = SITE_PATH . DIRECTORY_SEPARATOR . "logs";

                $nome_arquivo = "log_acessos.log";

                //Se ainda não existir a pasta, cria
                if(!is_dir($dir))
                {
                    if(!mkdir($dir,0755,TRUE))
                    {
                        die("Erro ao criar o diretório. " . $dir);	
                    }
                }

                //VERIFICA O TAMANHO DO ARQUIVO DE LOG
                if(file_exists($dir . DIRECTORY_SEPARATOR . $nome_arquivo))
                {
                    $size = filesize($dir . DIRECTORY_SEPARATOR . $nome_arquivo);

                    //PERCORRE OS SUFIXOS DE ARQUIVOS DE LOG PARA INCREMENTAR O SUFIXO DE BACKUP
                    for($i=1;$i<=999;$i++)
                    {
                        if(file_exists($dir . DIRECTORY_SEPARATOR . $nome_arquivo . "." . sprintf("%03d",$i)))
                        {
                            $num_atual = sprintf("%03d",$i + 1);

                            break;
                        }
                    }

                    //NÃO ACHOU O ARQUIVO
                    if(empty($num_atual))
                    {
                        $num_atual = sprintf("%03d",1);
                    }

                    //SE MAIOR QUE O TAMANHO DEFINIDO, RENOMEIA O ARQUIVO PRINCIPAL PARA BACKUP
                    if($size >= LOG_SIZE)
                    {
                        if(!rename($dir . DIRECTORY_SEPARATOR . $nome_arquivo, $dir . DIRECTORY_SEPARATOR . $nome_arquivo . "." . $num_atual))
                        {
                            die("Erro ao renomear o arquivo.");
                        }
                    }
                }

                $handle = fopen($dir . DIRECTORY_SEPARATOR . $nome_arquivo , "a");

                if (is_writable($dir . DIRECTORY_SEPARATOR . $nome_arquivo)) 
                {
                    $texto = '';

                    $IP = '';

                    $host = '';

                    $proxy = '';

                    /*
                    if ($_SERVER["HTTP_X_FORWARDED_FOR"] != "")
                    {
                        $IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
                        $proxy = $_SERVER["REMOTE_ADDR"];
                        $host = @gethostbyaddr($_SERVER["HTTP_X_FORWARDED_FOR"]);
                    }
                    else
                    {
                        $IP = $_SERVER["REMOTE_ADDR"];
                        $proxy = '';
                        $host = @gethostbyaddr($_SERVER["REMOTE_ADDR"]);
                    }
                    */
                    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) 
                    {
                        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                    }
                    
                    $client  = @$_SERVER['HTTP_CLIENT_IP'];
                    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
                    $remote  = $_SERVER['REMOTE_ADDR'];
                    
                    if(filter_var($client, FILTER_VALIDATE_IP)) 
                    { 
                        $IP = $client; 
                    }
                    elseif(filter_var($forward, FILTER_VALIDATE_IP)) 
                    { 
                        $IP = $forward; 
                    }
                    else 
                    { 
                        $IP = $remote; 
                    }

                    $host = @gethostbyaddr($IP);

                    $texto .= 'Arquivo: ' . $arquivo . ', Host: ' . $host . ', IP: ' . $IP . ', Data: ' . date('Y-m-d H:i:s') . CRLF;
                    
                    // Em nosso exemplo, nós vamos abrir o arquivo $filename
                    // em modo de adição. O ponteiro do arquivo estará no final
                    // do arquivo, e é pra lá que $conteudo irá quando o 
                    // escrevermos com fwrite().               
                    // Escreve $conteudo no nosso arquivo aberto.
                    if (fwrite($handle, $texto) === FALSE)
                    {
                        die("Não foi possível escrever no arquivo ". $dir . DIRECTORY_SEPARATOR . $nome_arquivo);
                    }
                
                    fclose($handle);               
                } 
                else 
                {
                    die("O arquivo ".$dir . DIRECTORY_SEPARATOR . $nome_arquivo . " não pode ser criado/alterado");
                }
            }
        }
	}

?>