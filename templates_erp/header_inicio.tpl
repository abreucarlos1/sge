<div class="container">
    <div class="item header"><smarty>$nome_sistema</smarty><smarty>$nome_formulario</smarty> - <smarty>$revisao_documento</smarty></div>
    <div class="item logo"></div>
    <div class="item nav"><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><label class="link_1"><smarty>$smarty.session.login</smarty></label><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><a href="#" onclick="troca_senha('<smarty>$smarty.session.login</smarty>','<smarty>$smarty.session.id_usuario</smarty>')" class="link_1">Trocar senha</a><img class="mini_seta" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>mini_seta.png" /><a href="logout.php" class="link_1">Sair</a></div>
    