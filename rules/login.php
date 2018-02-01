<?php
@require_once("../include/functions.php");
responseMethod();

/****************************
 * Methods defined for use. *
 ****************************/
function login( $parameters ) {
	unset($_SESSION);

	$arr = array();
	$arr['page'] = "";
	$arr['login'] = false;

	$pag = mb_strtoupper($parameters["page"]);
	$usr = mb_strtoupper($parameters["username"]);
	$psw = strtolower($parameters["password"]);

	//Verificacao de Usuario/Senha
	if ( isset($usr) && !empty($usr) ):
		$barDecode	= PATTERNS::getBars()->decode($usr);
		$usrClube	= ($barDecode["lg"] == PATTERNS::getBars()->getLength() &&
					   $barDecode["cp"] == PATTERNS::getBars()->getClubePrefix() &&
					   PATTERNS::getBars()->has("id",$barDecode["split"]["id"])
					);

		$result = checkUser($usr, $pag);

		//SE NAO ENCONTROU E O CODIGO TEM OS CARACTERES MINIMOS PARA USUARIO DO CLUBE
		if ($result->EOF && $usrClube):
			$usrClube = (sha1(strtolower($usr)) == $psw);

			$usr = PATTERNS::getBars()->encode(array(
				"ni" => $barDecode["ni"]
			));
			if ($usrClube):
				$psw = sha1(strtolower($usr));
			endif;
			$result = checkUser($usr, $pag);

		//SE NAO ENCONTROU
		elseif ($result->EOF):

			//VERIFICA SE CPF É DE UM MEMBRO ATIVO
			$result = checkMemberByCPF($usr);
			if (!$result->EOF):
				$usuarioID = $result->fields["ID_USUARIO"];
				$usr = $result->fields["CD_USUARIO"];
				$psw = $result->fields["DS_SENHA"];

				if (is_null($usuarioID) &&
				   (!is_null($result->fields["CD_CARGO"]) || !is_null($result->fields["CD_CARGO2"])) ):

					$usr = PATTERNS::getBars()->encode(array(
						"ni" => $result->fields["ID_MEMBRO"]
					));
					$psw = sha1(strtolower($usr));

					fInsertUser( $usr, $result->fields['NM'], $psw, $result->fields['ID_CAD_PESSOA'] );

					PROFILE::applyCargos(
						$result->fields['ID_CAD_PESSOA'],
						$result->fields["CD_CARGO"],
						$result->fields["CD_CARGO2"]
					);
				endif;

				return login( array(
					"page"		=> $pag,
					"username"	=> $usr,
					"password"	=> $psw ) );

			//VERIFICA SE RESPONSAVEL TEM ALGUM DEPENDENTE ATIVO e SE CPF CONSTA COMO RESPONSAVEL
			else:

				$resp = RESPONSAVEL::verificaRespByCPF($usr);
				if ( !is_null($resp) && existeMenorByRespID($resp["ID_CAD_PESSOA"]) ):
					$psw = sha1(str_replace("-","",str_replace(".","",$usr)));
					fInsertUserProfile( fInsertUser( $usr, $resp["NM"], $psw, null ), 10 );

					return login( array(
						"page"		=> $pag,
						"username"	=> $usr,
						"password"	=> $psw ) );

				endif;
			endif;

		endif;

		//SE NAO ENCONTROU USUARIO E SENHA E EH MEMBRO DO CLUBE COM APRENDIZADO OU HISTORICO.
		if ($usrClube && $result->EOF):

			//VERIFICA SE ESTÁ ATIVO
			$rsHA = CONN::get()->Execute("SELECT ID_CAD_PESSOA, NM FROM CON_ATIVOS WHERE ID_CLUBE = ? AND ID_MEMBRO = ?", array( $barDecode["ci"], $barDecode["ni"] ) );
			if (!$rsHA->EOF):
				fInsertUserProfile( fInsertUser( $usr, $rsHA->fields['NM'], $psw, $rsHA->fields['ID_CAD_PESSOA'] ), 0 );

				return login( array(
					"page" =>		$pag,
					"username" =>	$usr,
					"password" =>	$psw ) );
			endif;

		//SE EXISTE O USUARIO DIGITADO.
		elseif (!$result->EOF):

			if ($usrClube):
				//VERIFICA SE ESTÁ ATIVO
				$rsHA = CONN::get()->Execute("SELECT CD_CARGO, CD_CARGO2 FROM CON_ATIVOS WHERE ID_CLUBE = ? AND ID_MEMBRO = ?", array( $barDecode["ci"], $barDecode["ni"] ) );
				if ($rsHA->EOF):
					$psw = null;
				endif;
				PROFILE::applyCargos(
					$result->fields['ID_CAD_PESSOA'], 
					$rsHA->fields["CD_CARGO"], 
					$rsHA->fields["CD_CARGO2"]
				);
			else:
				$resp = RESPONSAVEL::verificaRespByCPF($usr);
				if (!is_null($resp) && !existeMenorByRespID($resp["ID_CAD_PESSOA"])):
					fDeleteUserAndProfile( $result->fields["ID_USUARIO"], 10 );
					return $arr;
				endif;
			endif;

			$password = $result->fields['DS_SENHA'];

			if ($password == $psw):
				PROFILE::fSetSessionLogin($result);

				CONN::get()->Execute("
					UPDATE CAD_USUARIOS SET 
						DH_ATUALIZACAO = NOW()
					WHERE ID_USUARIO = ?
				", array( $result->fields['ID_USUARIO'] ) );

				if ( $pag == "READDATA" ):
					$arr['page'] = PATTERNS::getVD()."readdata.php";
				else:
					$arr['page'] = PATTERNS::getVD()."admin/index.php";
				endif;
				$arr['login'] = true;
			endif;
		endif;
	endif;

	if (!$arr['login']):
		sleep(rand(5,15));
	endif;

	return $arr;
}

function fInsertUser( $usr, $nm, $psw, $pessoaID ){
	CONN::get()->Execute("
			INSERT INTO CAD_USUARIOS(
				CD_USUARIO,
				DS_USUARIO,
				DS_SENHA,
				ID_CAD_PESSOA
			) VALUES( ?, ?, ?, ? )",
	array( $usr, $nm, $psw, $pessoaID ) );
	return CONN::get()->Insert_ID();
}

function fInsertUserProfile( $userID, $profileID ){
	$rs = CONN::get()->Execute("
		SELECT 1
		  FROM CAD_USU_PERFIL
		 WHERE ID_CAD_USUARIOS = ?
		   AND ID_PERFIL = ?
	", array( $userID, $profileID ) );
	if ($rs->EOF):
		CONN::get()->Execute("
			INSERT INTO CAD_USU_PERFIL(
				ID_CAD_USUARIOS,
				ID_PERFIL
			) VALUES( ?, ? )
		", array( $userID, $profileID ) );
	endif;
}

function fDeleteUserAndProfile( $userID, $profileID ){
	CONN::get()->Execute("
		DELETE FROM CAD_USU_PERFIL
		 WHERE ID_CAD_USUARIOS = ?
		   AND ID_PERFIL = ?
	", array( $userID, $profileID ) );

	CONN::get()->Execute("
		DELETE FROM CAD_USUARIOS
		 WHERE ID_USUARIO = ?
	", array( $userID ) );
}

function checkMemberByCPF($cpf){
	return CONN::get()->Execute("
		SELECT cu.ID_USUARIO, cu.CD_USUARIO, cu.DS_USUARIO, cu.DS_SENHA, cu.CLASS,
			   ca.ID_CAD_PESSOA, ca.TP_SEXO, ca.CD_CARGO, ca.CD_CARGO2, ca.NM, ca.ID_MEMBRO, ca.EMAIL
		  FROM CON_ATIVOS ca
	 LEFT JOIN CAD_USUARIOS cu ON (cu.ID_CAD_PESSOA = ca.ID_CAD_PESSOA)
		 WHERE ca.NR_CPF = ?
	",array( fClearBN($cpf) ) );
}

function checkUser($cdUser, $pag){

	//VERIFICA SE PRECISA ATUALIZAR USUARIO
	$rs = CONN::get()->Execute("
		SELECT cu.ID_USUARIO, cp.ID
		FROM CAD_USUARIOS cu
		INNER JOIN CAD_PESSOA cp ON (cp.NR_CPF = cu.CD_USUARIO)
		WHERE cu.CD_USUARIO = ?
		  AND cu.ID_CAD_PESSOA IS NULL
	", array($cdUser) );
	if (!$rs->EOF):
		CONN::get()->Execute("
			UPDATE CAD_USUARIOS SET ID_CAD_PESSOA = ? WHERE ID_USUARIO = ?
		", array( $rs->fields["ID"], $rs->fields["ID_USUARIO"] ) );
	endif;

	return CONN::get()->Execute("
		SELECT cu.ID_USUARIO, cu.CD_USUARIO, cu.DS_USUARIO, cu.DS_SENHA, cu.CLASS,
			   cm.ID_CAD_PESSOA, cm.ID AS ID_CAD_MEMBRO, cm.ID_CLUBE, cm.ID_MEMBRO,
			   cp.TP_SEXO, cp.EMAIL
		  FROM CAD_USUARIOS cu
		LEFT JOIN CAD_PESSOA cp ON (cp.ID = cu.ID_CAD_PESSOA OR cp.NR_CPF = ?)
		LEFT JOIN CAD_MEMBRO cm ON (cm.ID_CAD_PESSOA = cp.ID)
	". ($pag == "READDATA" ? " INNER JOIN CAD_USU_PERFIL cuf ON (cuf.ID_CAD_USUARIOS = cu.ID_USUARIO AND cuf.ID_PERFIL = 2) " : "") ."
		 WHERE cu.CD_USUARIO = ?
	", array( $cdUser, $cdUser ) );
}

function setTheme( $parameters ) {
	session_start();
	CONN::get()->Execute("
		UPDATE CAD_USUARIOS SET CLASS = ? WHERE ID_USUARIO = ?
	", array( $parameters["theme"], $_SESSION['USER']["ID_USUARIO"] ) );
}

function getMenu() {
	session_start();
	return PROFILE::montaMenu();
}

//28550424889
function logout() {
	session_start();
	session_destroy();
	unset($_SESSION);
	return array('logout' => true);
}
?>
