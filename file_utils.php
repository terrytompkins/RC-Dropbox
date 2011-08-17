<!-- * (c) 2011, TransWeb Tools - Terry M. Tompkins - http://transwebtools.com
	 * Licensed under Apache License, Version 2.0: http://www.apache.org/licenses/LICENSE-2.0
	 * Revision: 16-August-2011
-->

<?php
$MAX_UPLOAD_SIZE="100M";
$relative_upload_dir = "uploads";
$upload_dir = getcwd() . "/$relative_upload_dir";
$upload_uri = getBaseUrl() . $relative_upload_dir;

function GetBaseURI() {
	$url = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
	$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(dirname($url), '/').'/';
	return $url;
}

function getBaseUrl()
{
	$currentPath = $_SERVER['PHP_SELF'];
	$pathInfo = pathinfo($currentPath);
	$hostName = $_SERVER['HTTP_HOST'];
	$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

	// return in format: http://localhost/myproject/
	return $protocol.$hostName.$pathInfo['dirname']."/";
}

function process_dir($dir, $recursive = FALSE) {
	if (is_dir($dir)) {
		for ($list = array(), $handle = opendir($dir); (FALSE !== ($file = readdir($handle)));) {
			if (($file != '.' && $file != '..') && (file_exists($path = $dir . '/' . $file))) {
				if (is_dir($path) && ($recursive)) {
					$list = array_merge($list, process_dir($path, TRUE));
				} else {
					$entry = array('filename' => $file, 'dirpath' => $dir);

					//---------------------------------------------------------//
					//                     - SECTION 1 -                       //
					//          Actions to be performed on ALL ITEMS           //
					//-----------------    Begin Editable    ------------------//

					$entry['modtime'] = filemtime($path);

					//-----------------     End Editable     ------------------//
					do
						if (!is_dir($path)) {
							//---------------------------------------------------------//
							//                     - SECTION 2 -                       //
							//         Actions to be performed on FILES ONLY           //
							//-----------------    Begin Editable    ------------------//

							$entry['size'] = filesize($path);
							/*
							  if (strstr(pathinfo($path,PATHINFO_BASENAME),'log')) {
							  if (!$entry['handle'] = fopen($path,r)) $entry['handle'] = "FAIL";
							  }
							 */

							//-----------------     End Editable     ------------------//
							break;
						} else {
							//---------------------------------------------------------//
							//                     - SECTION 3 -                       //
							//       Actions to be performed on DIRECTORIES ONLY       //
							//-----------------    Begin Editable    ------------------//
							//-----------------     End Editable     ------------------//
							break;
						} while (FALSE);
					$list[] = $entry;
				}
			}
		}
		closedir($handle);
		return $list;
	} else
		return FALSE;
}

function show_uploaded_file_links($dir, $delete_files_var="files_to_delete") {
	global $upload_uri;
	$result = process_dir($dir, FALSE);
	if (count($result) < 1)
		return;

	echo "<table border=1>";
	echo "<tr><th>Filename</th><th>Modify Date</th><th>File size</th><th><input type=\"submit\" value=\"Delete\" class=\"btn\"/></th></tr>";
	foreach ($result as $file) {
		echo "<tr>";
		$fullpath = $file['dirpath'] . '/' . $file['filename'];
		$fileurl = "$upload_uri/" . $file['filename'];
		$fileurl_display_text = wordwrap($file['filename'], 70, "<br />\n", true);

		echo "<td><a href=\"$fileurl\" target=_BLANK>" . $fileurl_display_text . "</a>";
		echo "<td>" . date("d-F-Y H:i:s", filemtime($fullpath)) . "</td>";
		echo "<td>" . number_format(filesize($fullpath)) . "</td>";
		echo "<td style=\"text-align:center\"><input type=\"checkbox\" name=\"${delete_files_var}\"  value=\"" . $file['filename'] . "\" />";

		/* if it's a log file, dump contents
		  if (is_resource($file['handle'])) {
		  echo "\n\nFILE (" . $file['dirpath'].'/'.$file['filename'] . "):\n\n" . fread($file['handle'];
		  fclose($file['handle']);
		  }
		 */
		echo "</tr>";
	}
	echo "</table>";
}

/*
 // The commented out code below may be used at some future point when file permission management is added.
function permission($filename) {
	$perms = fileperms($filename);

	if (($perms & 0xC000) == 0xC000) {
		$info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
		$info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
		$info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
		$info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
		$info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
		$info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
		$info = 'p';
	} else {
		$info = 'u';
	}

	// владелец
	$info .= ( ($perms & 0x0100) ? 'r' : '-');
	$info .= ( ($perms & 0x0080) ? 'w' : '-');
	$info .= ( ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

	// группа
	$info .= ( ($perms & 0x0020) ? 'r' : '-');
	$info .= ( ($perms & 0x0010) ? 'w' : '-');
	$info .= ( ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

	// все
	$info .= ( ($perms & 0x0004) ? 'r' : '-');
	$info .= ( ($perms & 0x0002) ? 'w' : '-');
	$info .= ( ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

	return $info;
}

function dir_list($dir) {
	if ($dir[strlen($dir) - 1] != '/')
		$dir .= '/';

	if (!is_dir($dir))
		return array();

	$dir_handle = opendir($dir);
	$dir_objects = array();
	while ($object = readdir($dir_handle))
		if (!in_array($object, array('.', '..'))) {
			$filename = $dir . $object;
			$file_object = array(
			    'name' => $object,
			    'size' => filesize($filename),
			    'perm' => permission($filename),
			    'type' => filetype($filename),
			    'time' => date("d F Y H:i:s", filemtime($filename))
			);
			$dir_objects[] = $file_object;
		}

	return $dir_objects;
}
*/
?>
