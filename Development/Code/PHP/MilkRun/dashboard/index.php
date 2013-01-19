<?php include("../common/db.php")?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include("../common/head.php")?>
		<script type="text/javascript" src="../js/dashboard.js"></script>
	</head>
	<body class='body'>
		<div id="header">
			<?php include("../common/header.php")?>
		</div>
		<div id='content1'>
			<!--<div id='sideNav' class='side-nav'>
				<?php include("sideNav.php"); ?>
			</div>-->
			<div id='main-div' class='main-content'>
				<?php include("dashboard.php"); ?>
			</div>
			<div style="clear:both;">&nbsp;</div>
		</div>
		<footer>
			<?php include("../common/footer.php")?>
		</footer>
	</body>
</html>

