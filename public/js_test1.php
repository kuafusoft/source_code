<html>
	<head>
		<script src="js/lib/jquery-1.6.4.min.js" type="text/javascript"></script>
		<script src="js/lib/jqgrid_3.8.2/js/jquery.jqGrid.min.js" type="text/javascript"></script>
		<script src="js/xt.js" type="text/javascript"></script>
		<script src="js/kf_tool.js" type="text/javascript"></script>
		<script src="js/grid_factory.js" type="text/javascript"></script>
	</head>
	<body>
		<script type="text/javascript">
		function test(){
			var tool = new kf_tool();
			var task = grid_factory.get('useradmin', 'task');
			task.load();
		}
		</script> 
		<input type="button" value="click" onclick="javascript:test();" />
		<div id='mainContent'>
			<table id='mainContent_useradmin_task_list'></table>
			<div id='mainContent_useradmin_task_pager'></div>
		
		</div>
	</body>
</html>