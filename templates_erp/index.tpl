<smarty>include file="html_conf.tpl"</smarty>
<smarty>include file="header_index.tpl"</smarty>
<form name="frm_login" id="frm_login" class="formulario_index" method="POST" action="<smarty>$smarty.server.PHP_SELF</smarty>">
    
    <div class="campos_index">
        <div class="row">
            <div class="col">
                <label for="login">Usuário</label>
                <input name="login" id="login" type="text" class="form-control" style="text-transform:none;" placeholder="Usuário" value="<smarty>$user</smarty>" onfocus="document.getElementById('mensagem').innerHTML='';">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="senha">Senha</label>
                <input name="senha" id="senha" type="password" class="form-control" style="text-transform:none;" placeholder="Senha" value="" onkeypress="if(event.keyCode==13){xajax_autenticacao(xajax.getFormValues('frm_login'));}">
            </div>            
        </div>
        <div class="row">
            <div class="col">
                <span class="badge badge-info" style="cursor: help;" onclick="esqueceusenha();">Esqueci minha senha</span>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button type="button" id="btnlogin" name="btnlogin" class="btn btn-outline-primary btn-sm" autofocus onclick="xajax_autenticacao(xajax.getFormValues('frm_login'));">Login</button>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <span class="badge badge-danger" id="mensagem"></span>
            </div>
        </div>
    </div>
</form>
<smarty>include file="footer.tpl"</smarty>
