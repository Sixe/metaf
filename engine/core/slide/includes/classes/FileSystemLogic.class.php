<?php

class FileSystemLogic {

	/*
	 * Returns a filesystem object from relay database according to its filename (which is supposed to be unique).
	 */
	function getByFilename($filename){
		if(isset($filename) && strlen($filename)>0){
			$dao = new FileSystemDAO();
			$sql = "SELECT * FROM relay_filesystem WHERE filename = '".$filename."' LIMIT 0,1";
			return $dao->getOneBySQL($sql);
		}
		else
			return null;
	}

	/*
	 * Returns a filesystem object from relay database according to its id.
	 */
	function getById($id){
		if(isset($id) && strlen($id)>0 && is_numeric($id)){
			$dao = new FileSystemDAO();
			return $dao->getById($id);
		}
		else
			return null;
	}

	/*
	 * Add a new download to the download counter to the filesystem with this id.
	 */
	function addDownload($id){
		if(isset($id) && is_numeric($id)){
			$dao = new FileSystemDAO();
			$sql = "UPDATE relay_filesystem SET downloads = downloads+1 WHERE id = '".$id."'";
			$dao->exec($sql);
			return true;
		}
		else
			return false;
	}

}//end of class

?>