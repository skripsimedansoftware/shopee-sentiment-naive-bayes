<div class="content-wrapper">
	<section class="content-header">
		<h1><?= $code ?> <small><?= $header ?></small></h1>
		<ol class="breadcrumb">
			<li><a href="<?= module_link() ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
			<li class="active">Error</li>
		</ol>
	</section>
	<section class="content">
		<div class="box">
			<div class="box-header with-border">
				<h3 class="box-title"><?= $header ?></h3>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
					<button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
				</div>
			</div>
			<div class="box-body">
				<?= $message ?>
			</div>
		</div>
	</section>
</div>
