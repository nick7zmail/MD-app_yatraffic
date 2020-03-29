<?php
/**
* Пробки от Яндекс
*
*
* @package project
* @author Nick7zmail
*/
//
//
class app_yatraffic extends module {
/**
* blank
*
* Module class constructor
*
* @access private
*/
function app_yatraffic() {
  $this->name="app_yatraffic";
  $this->title="Пробки от Яндекс";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data='') {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
	global $subm;
	if($subm == 'getTraffic'){
		$this->get_traffic(gg('yt_settings.reg_id'));
	} else if($subm == 'settings'){
		$this->save_settings();
		$this->get_settings($out);
	} else if($subm == 'routes'){
		$this->save_routes();
		$this->get_routes($out);
	}

	if ($this->view_mode == '') {
		$this->get_settings($out);
		$this->view_traffic($out);
	} else if($this->view_mode == 'settings') {
		$this->get_settings($out);
	} else if($this->view_mode == 'routes_settings') {
		$this->get_routes($out);
	} else if($this->view_mode == 'update_reg') {
		$this->update_reg($out);
	}


}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
 $this->view_traffic($out);
}

function view_traffic(&$out) {
$routes = $this->routes;
$map = $this->map;
if($routes=="1") {
		$out["apikey"] = gg('yt_settings.apikey');
		for ($i = 1; $i <= 11; $i++) {
			$out["ROUTES"]["route".$i] = gg('yt_settings.route'.$i);
			$out["ROUTES"]["route".$i."_name"] = gg('yt_settings.route'.$i.'_name');
		}
		$out["ROUTES"]["height"] = gg('yt_settings.height');
} elseif($routes=="2") {
		$out["apikey"] = gg('yt_settings.apikey');
		for ($i = 1; $i <= 11; $i++) {
			$out["MULTI_ROUTES"]["route".$i] = gg('yt_settings.route'.$i);
			$out["MULTI_ROUTES"]["route".$i."_name"] = gg('yt_settings.route'.$i.'_name');
			$out["MULTI_ROUTES"]["meth".$i] = gg('yt_settings.route'.$i.'_method');
		}
		if ($map == "on") {$out["MULTI_ROUTES"]["map"]= gg('yt_settings.height');} else {$out["MULTI_ROUTES"]["map"]= "0";}
} else {
$url_ico='/templates/app_yatraffic/icons/';
		if (!gg('yt_settings.c_val') == '') {
		$out["TRAFFIC"]["city_title"] = gg('yt_settings.c_val');
		}
		else
		{
		$out["TRAFFIC"]["city_title"] = gg('yt_info.city_title');
		}
		$out["TRAFFIC"]["level"] = gg('yt_info.level');
		$out["TRAFFIC"]["icon"] = gg('yt_info.icon');
		$out["TRAFFIC"]["time"] = gg('yt_info.time');
		$out["TRAFFIC"]["val"] = gg('yt_info.val');
		$out["TRAFFIC"]["tend"] = gg('yt_info.tend');
		$out["TRAFFIC"]["trafficIco"] = $url_ico.gg('yt_info.icon').'.png';
		}
}

function get_traffic($reg_id) {
$data_file='https://export.yandex.ru/bar/reginfo.xml?region='.$reg_id; // адрес xml файла
$xml = simplexml_load_file($data_file); // раскладываем xml на массив
//Выставляем переменные
sg('yt_info.city_title', $xml->region->title);
sg('yt_info.level', $xml->traffic->region->level);
sg('yt_info.icon', $xml->traffic->region->icon);
sg('yt_info.time', $xml->traffic->region->time);
sg('yt_info.val', $xml->traffic->region->hint);
sg('yt_info.tend', $xml->traffic->region->tend);
runScript(gg('yt_settings.updScript'));
}
function get_routes(&$out)
{
  for ($i = 1; $i <= 11; $i++) {
  	$out['route'.$i] = gg('yt_settings.route'.$i);
  	$out['route'.$i.'_name'] = gg('yt_settings.route'.$i.'_name');
  	$out['meth'.$i] = gg('yt_settings.route'.$i.'_method');
  }
	$out["height"] = gg('yt_settings.height');
	$out["apikey"] = gg('yt_settings.apikey');
}

function get_settings(&$out)
{
	$out["reg_id"] = gg('yt_settings.reg_id');
	$out["c_val"] = gg('yt_settings.c_val');
	$out["updateTime"] = gg('yt_settings.updateTime');
	$out["script"] = gg('yt_settings.updScript');
	$out["city_title"] = gg('yt_info.city_title');
	$out["time"] = gg('yt_info.time');
}
function update_reg(&$out)
{
  $data_file='https://export.yandex.ru/bar/reginfo.xml';
  $xml = simplexml_load_file($data_file);
  sg('yt_settings.reg_id', $xml->info->region['id']);
}
function save_settings()
{
	global $reg_id;
	global $c_val;
	global $update_interval;
	global $script;

	if(isset($reg_id)) sg('yt_settings.reg_id',$reg_id);
	if(isset($c_val)) sg('yt_settings.c_val',$c_val);
	if(isset($script)) sg('yt_settings.updScript',$script);
	sg('yt_settings.updateTime',$update_interval);
	sg('yt_settings.countTime',1);
}
function save_routes()
{
  for($i=1;$i<=10;$i++) {
     global ${'route'.$i.'_name'};
     global ${'route'.$i};
     global ${'meth'.$i};
     sg('yt_settings.route'.$i.'_name',${'route'.$i.'_name'});
     if(${'route'.$i}!='') sg('yt_settings.route'.$i,${'route'.$i}); else sg('yt_settings.route'.$i, 'null');
     sg('yt_settings.route'.$i.'_method',${'meth'.$i});
  }
  global $height;
  sg('yt_settings.height',$height);
  global $apikey;
  sg('yt_settings.apikey',$apikey);
}

function processSubscription($event_name, $details='') {
  if ($event_name=='HOURLY') {
		$this->update();
  }
 }

function update() {
$updateTime = gg('yt_settings.updateTime');
if($updateTime > 0){
	$count = gg('yt_settings.countTime');
	if($count >= $updateTime){
		$this->get_traffic(gg('yt_settings.reg_id'));
		sg('yt_settings.countTime',1);
	} else {
		$count++;
		sg('yt_settings.countTime',$count);
	}
  }
}

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 subscribeToEvent($this->name, 'HOURLY');
 $className = 'ya_traffic';
 $objectName = array('yt_settings', 'yt_info');
 $objDescription = array('Настройки', 'Информация о пробках');
 /*$updCode = ;*/
 $rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($className) . "'");
	if (!$rec['ID']) {
		$rec = array();
		$rec['TITLE'] = $className;
		$rec['DESCRIPTION'] = 'Пробки от Яндекс';
		$rec['ID'] = SQLInsert('classes', $rec);
	}
		for ($i = 0; $i < count($objectName); $i++) {
		$obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objectName[$i]) . "'");
		if (!$obj_rec['ID']) {
			$obj_rec = array();
			$obj_rec['CLASS_ID'] = $rec['ID'];
			$obj_rec['TITLE'] = $objectName[$i];
			$obj_rec['DESCRIPTION'] = $objDescription[$i];
			$obj_rec['ID'] = SQLInsert('objects', $obj_rec);
		}
	}

  addClassMethod('ya_traffic', 'update', 'include_once(DIR_MODULES."app_yatraffic/app_yatraffic.class.php");
  $app_yatraffic=new app_yatraffic();
  $app_yatraffic->get_traffic(gg("yt_settings.reg_id"));');

  parent::install();
 }
// --------------------------------------------------------------------
}
?>
