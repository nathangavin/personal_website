<?php
	require_once("templates/main.php");
	
	function body() {
		?>
			<div class="option">
				<a href="./timetracker">Time Tracker</a>
			</div>
			<div class="option">
				<a href="./cv">CV</a>
			</div>
			<div class="option">
				<a href="./knowledge_management">Knowledge Management</a>
			</div>
			<div class="option">
				<a href="./incident_management">Incident Manangement</a>
			</div>
			<div class="option">
				<a href="./account_management">Account Management</a>
			</div>
		<?php
	}

	Main::template("Home", body);
?>

