<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- * (c) 2011, TransWeb Tools - Terry M. Tompkins - http://transwebtools.com
	 * Licensed under Apache License, Version 2.0: http://www.apache.org/licenses/LICENSE-2.0
	 * Revision: 17-January-2012
	 * CSS based on work from: Christina Chun - Digital Artist &amp; Web Developer - http://www.christinachun.com"
-->

<?php require_once "file_utils.php"; ?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
		<title>RC Dropbox</title>
		<link rel="stylesheet" href="themes/custom/style.css" type="text/css" id="" media="print, projection, screen" />
		<script type="text/javascript" src="jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="jquery.tablesorter.min.js"></script> 
		<script type="text/javascript" src="multifile.js"></script>
		<style type="text/css" title="layout" media="screen"> @import url("gg.css"); </style>
	</head>

	<body>
		<div id="container">
			<div id="header">
				<div class="headerText">RC Dropbox</div>
			</div>
			<div id="content">
				<div id="bodytext">
					<h2 class="headline">A. Select Files for Upload</h2>
					<form enctype="multipart/form-data" action="index.php" method = "post">
						<input id="my_file_element" type="file" name="file_1" class="btn" />
						<h2 class="headline">B. Files Queued for Upload</h2>
						<?php
						if (isset($_FILES)) {
							ini_set('memory_limit', $MAX_UPLOAD_SIZE);
							foreach($_FILES as $file)
							{
								if ($file['error'] == UPLOAD_ERR_OK) {
									$target_file = $upload_dir."/".$file['name'];
									if (file_exists($target_file)) unlink($target_file);
									move_uploaded_file($file['tmp_name'], $target_file);
								}
							}
						}
						?>
						<div id="files_list"></div>
						<h2 class="headline">C. Upload Selected Files</h2>
						<input type="submit" value="Upload Files" class="btn" />
					</form>
					<hr />

					<script>
						<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
						var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 3 );
						<!-- Pass in the file element -->
						multi_selector.addElement( document.getElementById( 'my_file_element' ) );
					</script>

					<div>
						<h2 class="headline">Files Available for Download:</h2>
						<form action="index.php" method="post">
						<?php
							if (isset($_POST['files_to_delete'])) {
								$files_to_delete = $_POST['files_to_delete'];
								foreach ($files_to_delete as $selected) {
									$fullpath = "$upload_dir/$selected";
									if (file_exists($fullpath)) unlink($fullpath);
								}
							}
							process_dir($upload_dir, FALSE);
							show_uploaded_file_links($upload_dir, "files_to_delete[]");
						?>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="footer">
			<a href="release.html" TARGET="release" onClick="window.open('', 'release', 'scrollbars=yes,resizable=yes,width=685,height=475') ">Release Notes</A>
			<div>Recreational Coding by: <a href="http://TransWebTools.com" title="Terry Tompkins">Terry Tompkins</a> &copy; 2012</div>
			<div>Revision: 17-January-2012</div>
		</div>
	</body>
</html>
