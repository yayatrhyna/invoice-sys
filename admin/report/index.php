<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>
<?php 
$from = isset($_GET['from']) ? $_GET['from'] : date("Y-m-d");
$to = isset($_GET['to']) ? $_GET['to'] : date("Y-m-d");
$type = isset($_GET['type']) ? $_GET['type'] : "all";
$type_arr = ['all'=>"All", "1"=>"Sales Invoices", "2" => "Services Invoices" ];
?>
<div class="card card-outline rounded-0 card-maroon">
	<div class="card-body">
		<div class="container-fluid">
			<form action="" id="filterForm">
				<div class="row align-items-end">
					<div class="col-lg-3 col-md-4 col-sm-12">
						<div class="form-group">
							<label for="from" class="control-label">From</label>
							<input type="date" id="from" name="from" value="<?= $from ?>" class="form-control form-control-sm rounded-0">
						</div>
					</div>
					
					<div class="col-lg-3 col-md-4 col-sm-12">
						<div class="form-group">
							<label for="to" class="control-label">To</label>
							<input type="date" id="to" name="to" value="<?= $to ?>" class="form-control form-control-sm rounded-0">
						</div>
					</div>
					
					<div class="col-lg-3 col-md-4 col-sm-12">
						<div class="form-group">
							<label for="type" class="control-label">Type</label>
							<select id="type" name="type" value="<?= $from ?>" class="custom-select custom-select-sm rounded-0">
								<option value="all" <?= $type == 'all' ? "selected" : "" ?>>All</option>
								<option value="1" <?= $type == '1' ? "selected" : "" ?>>Selling/Products Only</option>
								<option value="2" <?= $type == '2' ? "selected" : "" ?>>Services Only</option>
							</select>
						</div>
					</div>
					<div class="col-lg-3 col-md-4 col-sm-12">
						<div class="form-group">
							<button class="btn btn-default btn-sm bg-maroon"><i class="fa fa-filter"></i> Generate Report</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="card card-outline rounded-0 card-maroon">
	<div class="card-header">
		<h3 class="card-title">Report</h3>
		<div class="card-tools">
			<button class="btn btn-default border rounded-0" type="button" id="print"><i class="fa fa-print"></i> Print</button>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid" id="outPrint">
			<div class="lh-1">
				<h3 class="text-center"><?= $_settings->info('name') ?></h3>
				<h4 class="text-center">Invoice Reports</h4>
			</div>
			<hr>
			<table width="100%">
				<tr>
					<?php if($from == $to): ?>
					<td width="33.33%">
						<label for=""><b>Date:</b></label>
						<span>&nbsp;<?= date("F j, Y", strtotime($from)) ?></span>
					<?php else: ?>
					<td width="33.33%">
						<label for=""><b>From:</b></label>
						<span>&nbsp;<?= date("F j, Y", strtotime($from)) ?></span>
					</td>
					<td width="33.33%">
						<label for=""><b>To:</b></label>
						<span>&nbsp;<?= date("F j, Y", strtotime($to)) ?></span>
					</td>
					<?php endif ?>
					<td width="33.33%">
						<label for=""><b>Type:</b></label>
						<span>&nbsp;<?= $type_arr[$type] ?></span>
					</td>

				</tr>
			</table>
			<br>
			<?php 
			$where = "";
			if($from == $to)
				$where .= " date(`date_created`) = '{$from}' ";
			else
				$where .= " date(`date_created`) BETWEEN '{$from}' and '{$to}' ";
			if(is_numeric($type) && $type > 0){
				$where .= " and `type` = '{$type}' ";
			}
			$invoices = $conn->query("SELECT * FROM `invoice_list` where {$where} order by abs(unix_timestamp(`date_created`)) asc");
			?>
			<table class="table table-bordered">
				<colgroup>
					<col width="5%">
					<col width="25%">
					<col width="25%">
					<col width="15%">
					<col width="30%">
				</colgroup>
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">Invoice Code</th>
						<th class="text-center">Customer</th>
						<th class="text-center">Type</th>
						<th class="text-center">Total</th>
					</tr>
				</thead>
				<tbody>
					<?php if($invoices->num_rows > 0): ?>
						<?php $i = 1; ?>
						<?php $total = 0; ?>
						<?php while($row = $invoices->fetch_assoc()): ?>
						<?php $total +=$row['total_amount']; ?>
							<tr>
								<th class="text-center"><?= ($i++) ?></th>
								<td><?= $row['invoice_code'] ?></td>
								<td><?= ucwords($row['customer_name']) ?></td>
								<td><?= $type_arr[$row['type']] ?></td>
								<td class='text-right'><?=number_format( $row['total_amount'] , 2) ?></td>
							</tr>
						<?php endwhile; ?>
					<?php else: ?>
						<tr>
							<th class="text-center" colspan="5">No records found!</th>
						</tr>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<tr>
						<th class="text-center" colspan="4">Overall Total</th>
						<th class="text-right"><?= number_format( $total , 2); ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<noscript id="style">
	<style>
		html, body{
			min-height:unset !important;
			margin:none;
		}
	</style>
</noscript>
<script>
	$(document).ready(function(){
		$('form#filterForm').submit(function(e){
			e.preventDefault()
			start_loader()
			var data = $(this).serialize();
			location.href= "<?= base_url ?>admin/?page=report&"+data;
		})
		$('#print').click(function(){
			var toPrint = $('#outPrint').clone()[0].outerHTML
			var head = $('head').clone()
			var customCSS = $($("noscript#style").html()).clone().outerHTML
			var el = $("div")
			head.append(customCSS)
			start_loader()
			var nw = window.open("","blank", "width="+$(window).width()+", height="+$(window).height()+",left=0, top=0")
			nw.document.querySelector("head").innerHTML = head[0].outerHTML
			nw.document.querySelector("body").innerHTML = toPrint
			nw.document.close()
			setTimeout(() => {
				nw.print()
				setTimeout(() => {
					nw.close()
					end_loader()
				}, 300);
			}, 500);
		})
	})
</script>