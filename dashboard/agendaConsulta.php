<div class="row">
	<div class="col-lg-12">
		<h3 class="page-header">Consulta a Agenda</h3>
	</div>
</div>
<form method="post" id="agendaConsulta">
	<div class="row">
		<div class="form-group col-lg-2 col-md-2 col-sm-3 col-xs-5">
			<label for="cmANO" class="control-label">Ano:</label>
			<select field="ano" name="cmANO" id="cmANO" class="selectpicker form-control" opt-value="id" opt-label="ds" data-live-search="true" data-container="body" style="padding-right:0px"></select>
		</div>
	</div>
	<hr/>
	<div class="row" id="content"></div>
</form>
<script src="<?php echo PATTERNS::getVD();?>dashboard/js/agendaConsulta.js<?php echo "?".microtime();?>"></script>