<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta charset="utf-8>
<meta name="author" content="Carlos Abreu">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="Expires" content="0">

	<smarty>$xajax_javascript</smarty>

<title>::.. <smarty>$nome_sistema</smarty> ..::</title>

<link rel="stylesheet" href="<smarty>$smarty.const.DIR_PATH</smarty>includes/dhtmlx_403/codebase/dhtmlx.css" crossorigin="anonymous">


<!-- Bootstrap CSS -->
<link rel="stylesheet" href="<smarty>$smarty.const.DIR_PATH</smarty>includes/bootstrap/css/bootstrap.min.css" crossorigin="anonymous">

<!-- FONTAWESOME-->
<link href="<smarty>$smarty.const.DIR_PATH</smarty>includes/fontawesome/css/all.min.css" rel="stylesheet" crossorigin="anonymous">

<link rel="stylesheet" href="<smarty>$smarty.const.DIR_PATH</smarty>classes/classes.css">

<!-- <link rel="shortcut icon" href="favicon.ico" > -->

<!-- JQuery-->
<script src="<smarty>$smarty.const.DIR_PATH</smarty>includes/jquery/jquery-3.6.0.min.js" crossorigin="anonymous"></script>

<script src="<smarty>$smarty.const.DIR_PATH</smarty>includes/jquery-ui-1.12.1/jquery-ui.min.js" crossorigin="anonymous"></script>

<!-- BOOTSTRAP JS-->
<script src="<smarty>$smarty.const.DIR_PATH</smarty>includes/bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>

<!-- BOOTBOX JS-->
<!-- <script src="../includes/bootbox/bootbox.all.min.js" crossorigin="anonymous"></script> -->

<script src="<smarty>$smarty.const.DIR_PATH</smarty>includes/dhtmlx_403/codebase/dhtmlx.js" crossorigin="anonymous"></script>

<script src="<smarty>$smarty.const.DIR_PATH</smarty>includes/validacao.js" crossorigin="anonymous"></script>

<!-- <script src="../includes/utils.js" crossorigin="anonymous"></script> -->
<script src="<smarty>$smarty.const.DIR_PATH</smarty>includes/utils.js" crossorigin="anonymous"></script>
</head>

<body onload="<smarty>$body_onload</smarty>">

	      <!-- Loader -->
        <div id="div_loader" class="loader" style="display:none;">
        
          <!-- loader content -->
          <div class="loader-content">
            <!--<img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>ajax-loader.gif"/>-->
          </div>
        
        </div>

	<!-- Modal -->
	<input type="hidden" name="nModal" id="nModal" value="0">
	<div id="dvModal"></div>
