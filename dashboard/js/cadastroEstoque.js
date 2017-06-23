var dataTable = undefined;

$(document).ready(function(){
	$.fn.dataTable.moment( 'DD/MM/YYYY' );

	dataTable = $('#matEstTable')
		.DataTable({
			lengthChange: false,
			ordering: true,
			paging: false,
			scrollY: 330,
			searching: true,
			processing: true,
			language: {
				info: "_END_ itens em estoque",
				search: "",
				searchPlaceholder: "Procurar...",
				infoFiltered: " de _MAX_",
				loadingRecords: "Aguarde - carregando...",
				zeroRecords: "Dados indispon&iacute;veis para esta sele&ccedil;&atilde;o. Verifique opções de filtro.",
				infoEmpty: "0 encontrados"
			},
			ajax: {
				type	: "POST",
				url	: jsLIB.rootDir+"rules/cadastroEstoque.php",
				data	: function (d) {
						d.MethodName = "getEstoque",
						d.data = { 
								 filtro: 'T',
								 filters: jsFilter.jSON()
							}
					},
				dataSrc: "est"
			},
			order: [ 1, 'asc' ],
			columns: [
				{	data: "id"
				},
				{	data: "tp",
					type: 'ptbr-string',
					sortable: true,
					width: "15%"
				},
				{	data: "ds",
					type: 'ptbr-string',
					sortable: true,
					width: "75%"
				},
				{	data: "qt",
					sortable: true,
					width: "10%",
				}
			],
			columnDefs: [
				{
					targets: [ 0 ],
					visible: false,
					searchable: false
				}
			],
			select: {
				style: 'multi',
				selector: 'td:first-child'
			}
		})
	;
	
	$("#cadListaForm")
		.on('init.field.fv', function(e, data) {
			var $parent = data.element.parents('.form-group'),
			$icon   = $parent.find('.form-control-feedback[data-fv-icon-for="' + data.field + '"]');
			$icon.on('click.clearing', function() {
				if ( $icon.hasClass('glyphicon-remove') ) {
					data.fv.resetField(data.element);
				}
			});
		})
		.on('success.form.fv', function(e) {
			e.preventDefault();
		})	
		.formValidation({
			framework: 'bootstrap',
			icon: {
				valid: 'glyphicon glyphicon-ok',
				invalid: 'glyphicon glyphicon-remove',
				validating: 'glyphicon glyphicon-refresh'
			}
		})
		.submit( function(event) {
			var parameter = {
				frm: jsLIB.getJSONFields( $('#cadListaForm') )
			};
			jsLIB.ajaxCall( false, jsLIB.rootDir+"rules/cadastroEstoque.php", { MethodName : 'addEstoque', data : parameter } );
			dataTable.ajax.reload();
			$("#listaModal").modal('hide');
		})
	;
	
	$('#btnAdd').click(function(){
		var parameter = {
			domains : [ "tiposEstoque", "nomes" ]
		};
		var cg = jsLIB.ajaxCall( false, jsLIB.rootDir+"rules/listaCompras.php", { MethodName : 'getData', data : parameter }, 'RETURN' );
		jsLIB.populateOptions( $("#cmTipo"), cg.tipos );
		jsLIB.populateOptions( $("#cmNome"), cg.nomes );
		
		$('#divItem').visible(false);
		jsLIB.resetForm( $('#cadListaForm') );
		$("#qtItens").val(1);
		$("#listaModal").modal();
	});
	
	$('#cmTipo').change(function(){
		var value = $(this).val();
		var visible = value != '';
		if (visible){
			var parameter = {
				key : value,
				domains : [ "itensEstoque" ]
			};
			var cg = jsLIB.ajaxCall( false, jsLIB.rootDir+"rules/listaCompras.php", { MethodName : 'getData', data : parameter }, 'RETURN' );
			jsLIB.populateOptions( $("#cmItem"), cg.itens );
		}
		$('#divItem').visible(visible);
	});
	
	$("#qtItens").TouchSpin({
		verticalbuttons: true,
		verticalupclass: 'glyphicon glyphicon-plus',
		verticaldownclass: 'glyphicon glyphicon-minus'
	});
	
	$('#matEstTable tbody').on('click', 'tr', function () {
		$(this).toggleClass('selected');
		ruleBtnEdit();
	});	
	ruleBtnEdit(false);
	

});

function ruleBtnEdit( force ){
	var data = dataTable.rows('.selected').data();
	var selected = "";
	if (force == undefined){
		if (data.length == 1){
			selected = data[0].id;
			var es = jsLIB.ajaxCall( false, jsLIB.rootDir+"rules/cadastroEstoque.php", { MethodName : 'getItem', data : { id: selected } }, 'RETURN' );
			if (!es || !es.edit){
				selected = "";
			}
		}
	}
	$("#btnEdit")
		.attr("id-item",selected)
		.visible( selected != "" );
}