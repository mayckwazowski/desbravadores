<?php
@require_once("../include/filters.php");
?>
<div class="row">
	<div class="col-lg-12">
		<h3 class="page-header">Consulta de Aniversariantes</h3>
	</div>
</div>
<div class="col-lg-12">
	<div class="row">
	<?php fDataFilters( 
		array( 
			"filterTo" => "#birthTable",
			"filters" => 
				array( 
					array( "value" => "X", "label" => "Sexo" ),
					array( "value" => "C", "label" => "Classe" ),
					array( "value" => "G", "label" => "Grupo" ),
					array( "value" => "MA", "label" => "Mês de Aniversário" ),
					array( "value" => "U", "label" => "Unidade" )
				)
		) 
	);?>
	</div>
	<div class="row">
		<table class="compact row-border hover stripe display" style="cursor: pointer;" cellspacing="0" width="100%" id="birthTable">
			<thead>
				<tr>
					<th></th>
					<th>Nome Completo</th>
					<th>Unidade</th>
					<th>Dia/Mes</th>
					<th>Idade</th>
				</tr>
			</thead>
		</table>
		<br/>
	</div>
</div>
<script src="<?php echo $GLOBALS['VirtualDir'];?>dashboard/js/consultaAniversarios.js<?php echo "?".microtime();?>"></script>