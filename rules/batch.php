<?php
@require_once("../include/functions.php");
fConnDB();
//INICIO DA ROTINA DIARIA
$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.00-Iniciando rotina...')");
//LOG
$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.01.00-Inicio do tratamento de LOGs...')");
$GLOBALS['conn']->Execute("DELETE FROM LOG_BATCH WHERE DH < ( NOW() - INTERVAL 30 DAY )");
$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.01.99-Exclusão de LOGs antigos concluída.')");
//SECRETARIA
$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.02.00-Analisando Secretaria...')");
$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.02.99-Rotina de secretaria finalizada com Sucesso.')");
//TESOURARIA
//$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.03.00-Analisando Tesouraria...')");
//$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.03.99-Rotina de Tesouraria finalizada com Sucesso.')");
////FINAL DA ROTINA DIARIA
$GLOBALS['conn']->Execute("INSERT INTO LOG_BATCH(TP,DS) VALUES('DIÁRIA','01.99-Rotina finalizada com sucesso!')");
exit;
?>