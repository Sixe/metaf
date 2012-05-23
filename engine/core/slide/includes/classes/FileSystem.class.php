<?php

require_once ("slide/includes/classes/BaseDAO.class.php");

//**** Classe FileSystem Value Object ****
class FileSystemVO {
	var $id;
	var $filename;
	var $path;
	var $rpath;
	var $downloads;

	//**** Constructor ****
	function FileSystemVO() {}

	//**** GET / SET ****
	function getId() { return $this->id; }
	function getFilename() { return $this->filename; }
	function getPath() { return $this->path; }
	function getRPath() { return $this->rpath; }
	function getDownloads() { return $this->downloads; }

	function setId($v) { $this->id = $v; }
	function setFilename($v) { $this->filename = $v; }
	function setPath($v) { $this->path = $v; }
	function setRPath($v) { $this->rpath = $v; }
	function setDownloads($v) { $this->downloads = $v; }
}

//**** Classe FileSystem Data Access Object ****
class FileSystemDAO extends BaseDAO {
	//**** Constructor ****
	function FileSystemDAO() {
		include("slide/includes/inc/config.inc.php");
		parent::BaseDAO($DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWD);
	}

	//**** PUBLIC getAll ****
	function getAll() {
		$voList = array();
		$sql = "SELECT * FROM `relay_filesystem`";
		$this->exec($sql);
		while($rs = $this->getArray()) {
			$vo = new FileSystemVO();
			$vo = $this->getFromResultSet($vo, $rs);
			array_push($voList, $vo);
		}
		return $voList;
	}

	//**** PUBLIC getOneBySQL ****
	function getOneBySQL($sql) {
		$this->exec($sql);
		if($this->numRows() == 1) {
			$rs = $this->getArray();
			$vo = new FileSystemVO();
			$vo = $this->getFromResultSet($vo, $rs);
			return $vo;
		}
		else {
			return null;
		}
	}

	//**** PUBLIC getById ****
	function getById($id) {
		$sql = "SELECT * FROM `relay_filesystem` WHERE id = '".$id."'";
		$this->exec($sql);
		if($this->numRows() == 1) {
			$rs = $this->getArray();
			$vo = new FileSystemVO();
			$vo = $this->getFromResultSet($vo, $rs);
			return $vo;
		}
		else {
			return null;
		}
	}

	//**** PUBLIC getBySQL : returns an array of FileSystemVO objects ****
	function getBySQL($sql) {
		$voList = array();
		$this->exec($sql);
		while($rs = $this->getArray()) {
			$vo = new FileSystemVO();
			$vo = $this->getFromResultSet($vo, $rs);
			array_push($voList, $vo);
		}
		return $voList;
	}

	//**** PUBLIC getCalculatedFields : returns an array calculated fields (indexes are fields names) ****
	function getCalculatedFields($sql, $calculatedFieldsNames) {
		$result = array();
		$this->exec($sql);
		while($rs = $this->getArray()) {
			foreach($calculatedFieldsNames as $fieldName){
				$result[$fieldName] = $rs[$fieldName];
			}
		}
		return $result;
	}

	//**** PUBLIC delete : delete a record from a FileSystemVO ****
	function delete($vo) {
		$sql = "DELETE FROM `relay_filesystem` WHERE id = '".$vo->getId()."';";
		$this->exec($sql);
		return $this->affectedRows();
	}

	//**** PRIVATE getFromResultSet : fill a VO from a ResultSet ****
	function getFromResultSet($vo, $rs) {
		if(isset($rs['id'])) $vo->setId($rs['id']);
		if(isset($rs['filename'])) $vo->setFilename($rs['filename']);
		if(isset($rs['path'])) $vo->setPath($rs['path']);
		if(isset($rs['rpath'])) $vo->setRPath($rs['rpath']);
		if(isset($rs['downloads'])) $vo->setDownloads($rs['downloads']);
		return $vo;
	}

	//**** PUBLIC insert : insert a record from a FileSystemVO ****
	function insert($vo) {
		$sql = "INSERT INTO `relay_filesystem` (filename, path, rpath, downloads) VALUES ('".$vo->getFilename()."','".$vo->getPath()."','".$vo->getRPath()."','".$vo->getDownloads()."');";
		$this->exec($sql);
		return $this->affectedRows();
	}

	//**** PUBLIC update : update a record from a FileSystemVO ****
	function update($vo) {
		$sqlSetBlock = "";
		if(strlen($vo->getFilename())>0) $sqlSetBlock .= "filename = '".$vo->getFilename()."',";
		if(strlen($vo->getPath())>0) $sqlSetBlock .= "path = '".$vo->getPath()."',";
		if(strlen($vo->getRPath())>0) $sqlSetBlock .= "rpath = '".$vo->getRPath()."',";
		if(strlen($vo->getDownloads())>0) $sqlSetBlock .= "downloads = '".$vo->getDownloads()."',";

		if(strlen($sqlSetBlock)>0){
			$sqlSetBlock = substr($sqlSetBlock, 0, strlen($sqlSetBlock)-1);

			$sql = "UPDATE `relay_filesystem` SET  ".$sqlSetBlock."  WHERE id = '".$vo->getId()."';";
			$this->exec($sql);
		}

		return $this->affectedRows();
	}

}

?>