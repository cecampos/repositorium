<?php
/**
 * admin_documentos.php
 * 
 * CRUD sobre documentos
 * 
 * @package   controllers
 * @author    Mauricio Quezada <mquezada@dcc.uchile.cl>
 * @copyright Copyright (c) 2011 
 */

class AdminDocumentosController extends AppController {
  
  var $uses = array('Criteria', 'Document', 'CriteriasDocument', 'Tag', 'User');
  var $helpers = array('Text', 'Number');
  var $paginate = array(
	'CriteriasDocument' => array(
	  'limit' => 5,
	  'order' => array(
		'total_respuestas' => 'desc'
	  )
	)
  );

  function beforeFilter() {
	if(!$this->Session->check('User.esExperto') && !$this->Session->check('User.esAdmin')) {
		if($this->Session->check('User.id')) {
	  		$this->Session->setFlash('You do not have permission to access this page');
	  		$this->redirect(array('controller' => 'pages'));
		} else {
			$this->Session->setFlash('You do not have permission to access this page. Please log in if you are an administrator');
			$this->redirect(array('controller' => 'iniciar_sesion'));
		}
	}
	if($this->Session->check('CriteriasDocument.limit'))
		$this->paginate['CriteriasDocument']['limit'] = $this->Session->read('CriteriasDocument.limit');
	if($this->Session->check('CriteriasDocument.order'))
		$this->paginate['CriteriasDocument']['order'] = $this->_strToArray($this->Session->read('CriteriasDocument.order'));
  }

  function index() {
	$this->redirect(array('action'=>'validados'));
  }
  
  function _beforeList($confirmado, $all = false) {
  	$criterio_list = $this->Criteria->find('list');
  	$criterio_n = 1;  	
  	if(!empty($this->data)) {
  		if(!empty($this->data['Criteria']['pregunta'])) {
  			$criterio_n = $this->data['Criteria']['pregunta'];
  			$this->Session->write('CriteriasDocument.criterio', $criterio_n);  		
  		}
  		
  		if(!empty($this->data['Document']['limit'])) {
  			$this->paginate['CriteriasDocument']['limit'] = $this->data['Document']['limit'];
  			$this->Session->write('CriteriasDocument.limit', $this->data['Document']['limit']);
  		}
  		
  		if(!empty($this->data['CriteriasDocument']['order'])) {  			
  			$this->paginate['CriteriasDocument']['order'] = $this->_strToArray($this->data['CriteriasDocument']['order']);
  			$this->Session->write('CriteriasDocument.order', $this->_arrayToStr($this->paginate['CriteriasDocument']['order']));  			
  		}
  		
  		if(!empty($this->data['CriteriasDocument']['filter'])) {
  			$this->Session->write('CriteriasDocument.filter', $this->data['CriteriasDocument']['filter']);
  		}
  	}
  	
  	// filter
  	$cond = array();
  	if($this->Session->check('CriteriasDocument.filter')) {
  		$cond = $this->_strToFilterArray($this->Session->read('CriteriasDocument.filter'));
  	} else {
  		$cond = array('1' => '1');
  	}
  	
  	if($all) {
  		$data = $this->paginate('CriteriasDocument', array(
		  'CriteriasDocument.criteria_id' => ($this->Session->read('CriteriasDocument.criterio') ? $this->Session->read('CriteriasDocument.criterio') : $criterio_n),
		  $cond		  		  
		));	
  	} else {
  		$data = $this->paginate('CriteriasDocument', array(
		  'CriteriasDocument.criteria_id' => ($this->Session->read('CriteriasDocument.criterio') ? $this->Session->read('CriteriasDocument.criterio') : $criterio_n),
		  'CriteriasDocument.validated' => $confirmado,
		  $cond
		));  		
  	}
  	
	return compact('criterio_list', 'criterio_n', 'data');
  }
  
  function validados() {
  	$d = $this->_beforeList(1);
	$current = 'validados';
	$criterio_n = $this->Session->read('CriteriasDocument.criterio') ? $this->Session->read('CriteriasDocument.criterio') : $d['criterio_n'];
	$criterio_list = $d['criterio_list'];
	$data = $d['data'];	
	$limit = $this->Session->read('CriteriasDocument.limit') ? $this->Session->read('CriteriasDocument.limit') : $this->paginate['CriteriasDocument']['limit'];
	$ordering = $this->Session->read('CriteriasDocument.order') ? $this->Session->read('CriteriasDocument.order') : $this->_arrayToStr($this->paginate['CriteriasDocument']['order']);
	$filter = $this->Session->read('CriteriasDocument.filter') ? $this->Session->read('CriteriasDocument.filter') : 'all';
	
	$this->set(compact('criterio_n', 'criterio_list', 'data', 'current', 'limit', 'ordering', 'filter'));
	$this->render('listar');
  }

  function no_validados() {	
	$d = $this->_beforeList(0);
	$current = 'no_validados';
	$criterio_n = $this->Session->read('CriteriasDocument.criterio') ? $this->Session->read('CriteriasDocument.criterio') : $d['criterio_n'];
	$criterio_list = $d['criterio_list'];
	$data = $d['data'];
	$limit = $this->Session->read('CriteriasDocument.limit') ? $this->Session->read('CriteriasDocument.limit') : $this->paginate['CriteriasDocument']['limit'];
	$ordering = $this->Session->read('CriteriasDocument.order') ? $this->Session->read('CriteriasDocument.order') : $this->_arrayToStr($this->paginate['CriteriasDocument']['order']);
	$filter = $this->Session->read('CriteriasDocument.filter') ? $this->Session->read('CriteriasDocument.filter') : 'all';
		
	$this->set(compact('criterio_n', 'criterio_list', 'data', 'current', 'limit', 'ordering', 'filter'));
	$this->render('listar');
  }	
  
  function all() {
  	$d = $this->_beforeList(null, true);
	$current = 'all';
	$criterio_n = $this->Session->read('CriteriasDocument.criterio') ? $this->Session->read('CriteriasDocument.criterio') : $d['criterio_n'];
	$criterio_list = $d['criterio_list'];
	$data = $d['data'];
	$limit = $this->Session->read('CriteriasDocument.limit') ? $this->Session->read('CriteriasDocument.limit') : $this->paginate['CriteriasDocument']['limit'];
	$ordering = $this->Session->read('CriteriasDocument.order') ? $this->Session->read('CriteriasDocument.order') : $this->_arrayToStr($this->paginate['CriteriasDocument']['order']);
	$filter = $this->Session->read('CriteriasDocument.filter') ? $this->Session->read('CriteriasDocument.filter') : 'all';
	
	$this->set(compact('criterio_n', 'criterio_list', 'data', 'current', 'limit', 'ordering', 'filter'));
	$this->render('listar');
  }

  
  function add() {
	$this->redirect('/subir_documento');
  }
  
  function edit($id = null, $criterio = 1) {
  	if(empty($this->data)) {		
	  // stats
	  $this->data = $this->CriteriasDocument->find(
		'first',
		array(
			'conditions' => array(
				'CriteriasDocument.document_id' => $id,
				'CriteriasDocument.criteria_id' => $criterio
			)
		));
		
	  if(empty($this->data)) {
	  	$this->redirect('index');
	  }
	  
	  // tags
	  $raw_tags = $this->Tag->find('all', array('conditions' => array('Tag.document_id' => $id), 'recursive' => -1));
	  $tags = array();	  
	  foreach($raw_tags as $t)
		$tags[] = $t['Tag']['tag'];	  
	  $this->data['Document']['tags'] = implode($tags,', ');
	  
	  // user
	  $raw_user = $this->User->find('first', array('conditions' => array('User.id' => $this->data['Document']['user_id']), 'recursive' => -1));
	  $this->data['User']['user_id'] = $raw_user['User']['first_name'] . ' '. $raw_user['User']['last_name'] . ' ('.$raw_user['User']['email'].')';
	  
	  // criteria
	  $criterios_list = $this->Criteria->find('list');
	  $criterios_n = $criterio;
	  
	  $this->set('data',$this->data);
	  $this->set(compact('criterios_list', 'criterios_n'));	  
	} else {
	  // save stats info
	  if($this->CriteriasDocument->save($this->data))
	  	;	  
	  // save tags and basics
	  $this->Tag->deleteAll(array('Tag.document_id' => $id));
	  $this->data['Document']['id'] = $id;
	  if ($this->Document->saveWithTags($this->data)) {
		$this->Session->setFlash('Document "'. $this->data['Document']['title'] .'" edited successfully');
		CakeLog::write('activity', 'Document '.$id.'\'s content modified');
		$this->redirect($this->data['Action']['current']);
	  }
	  
	}
  }
  
  function edit_select_criteria($doc_id = null) {
  	if(!is_null($doc_id) and !empty($this->data)) {  		
  		$this->redirect(array('action' => 'edit/'.$doc_id.'/'.$this->data['Action']['select']));
  	}
  	$this->redirect($this->referer());
  }

  function view($id = null) {
  	$this->redirect(array('action' => 'edit/'.$id));
  }

  function remove($id = null, $redirect = true, $flash = true) {
	if (!is_null($id)) {
	  if($this->Document->delete($id)) {
		if($flash) $this->Session->setFlash('Document no. '.$id.' removed');
		CakeLog::write('activity', 'Document '.$id.' deleted');	
	  } else {
		if($flash) $this->Session->setFlash('There was an error at deleting the document');
	  }
	}
   	if($redirect) $this->redirect($this->referer());	
  }

  /* CriteriasDocument */
  function set_field($field = null, $id = null, $bool = null, $redirect = true) {
	if(!is_null($field) and !is_null($id) and !is_null($bool)) {
	  
	  /* blacklist */
	  if(in_array($field, array('id', 'document_id'))) {
		if($redirect) $this->redirect($this->referer());
	  }
  
	  $this->CriteriasDocument->set(array(
		'CriteriasDocument.id' => $id,
		$field => $bool
	  ));
	  
	  if(!$this->CriteriasDocument->save())
		return false;
	
	  CakeLog::write('activity', "CriteriasDocument id=$id modified: [field: $field, new value: $bool]");
	}	
	if($redirect) $this->redirect($this->referer());
  }

  function _reset_stats($id = null, $criteria = null) {
	if(!is_null($id) && !is_null($criteria)) {
	 $this->CriteriasDocument->updateAll(
	 	array(
			'CriteriasDocument.total_answers_1' => 0,
			'CriteriasDocument.total_answers_2' => 0,
		),
		array(
			'CriteriasDocument.document_id' => $id,
	  		'CriteriasDocument.criteria_id' => $criteria	
		));	  
	  CakeLog::write('activity', "Document $id modified: stats restarted");
	}
	return true;
  }
  
  function reset_only($id = null, $criteria = null) {
  	$this->_reset_stats($id, $criteria);
  	$this->Session->setFlash('Stats restarted successfully');
  	$this->redirect($this->referer());
  }
  
  function mass_edit($criteria = null) {
  	if(!empty($this->data) && !is_null($criteria)) {
  		/* reset stats */
  		if(strcmp($this->data['Action']['mass_action'], 'reset') == 0) {
  			foreach($this->data['Document'] as $d) {
  				$id = $d['document_id'];	
  				$this->_reset_stats($id, $criteria);  			 
  			}
  			$this->Session->setFlash('Documents\' statistics restarted successfully');
  			
  		/* validate docs */
  		} else if(strcmp($this->data['Action']['mass_action'], 'validate') == 0) {
  			foreach($this->data['Document'] as $doc) {  				
  				$id = $doc['document_id'];
  				$this->validate_document($id, $criteria ,false);
  			}  	
  			$this->Session->setFlash('Documents changed successfully');
  			
  		/* delete docs */
  		} else if(strcmp($this->data['Action']['mass_action'], 'delete') == 0) {
  			foreach($this->data['Document'] as $d) {
  				$id = $d['document_id'];
  				$this->remove($id, false, false);
  			}  			
  			$this->Session->setFlash('Documents removed successfully');
  			
  		/* default */
  		} else {
  			$this->Session->setFlash('Didn\'t do anything. Maybe you picked a wrong option');
  		}  		
  	}
  		
  	$this->redirect($this->referer());
  }
  
  function validate_document($id = null, $criteria = null, $redirect = true) {
  	
  	if(!is_null($id) && !is_null($criteria)) {  	  	  				
		$doc = $this->CriteriasDocument->find( 'first', array(
			'conditions' => array(
				'CriteriasDocument.document_id' => $id,
				'CriteriasDocument.criteria_id' => $criteria)			
		));
		
		// set respuesta_oficial to 1 if not set  				  				
		if($doc['CriteriasDocument']['is_positive'] === null) {			
			$this->set_field('respuesta_oficial_de_un_experto', $doc['CriteriasDocument']['id'], 1, false);
		} 				
		$this->set_field('validated', $doc['CriteriasDocument']['id'] , ($doc['CriteriasDocument']['validated']+1)%2, false);
  	}
  	if($redirect) $this->redirect($this->referer());
  }
  
  /* translates an array of ordering conditions to a string */
  function _arrayToStr($a = array()) {
 	if(array_key_exists('total_respuestas', $a)) {
 		if(strcmp($a['total_respuestas'], 'desc') == 0) {
 			return 'more-ans';
 		} else {
 			return 'less-ans';
 		} 			
 	} elseif(array_key_exists('consenso', $a)) {
 		if(strcmp($a['consenso'], 'desc') == 0) {
 			return 'more-cs';
 		} else {
 			return 'less-cs';
 		}
 	} else {
 		return null;
 	} 		
  }
  
  function _strToArray($ord = '') {
	if(strcmp('less-ans', $ord) == 0) {
		return array(
			'total_respuestas' => 'asc'
		);  				
	} elseif (strcmp('more-cs', $ord) == 0) {
		return array(
			'consenso' => 'desc'
		);
	} elseif (strcmp('less-cs', $ord) == 0) {
		return array(
			'consenso' => 'asc'
		);
	} else { // more-ans
		return array(
			'total_respuestas' => 'desc'
		);
	}  	
  }
  
  function _strToFilterArray($fil = '') {
  	if(strcmp('app', $fil) == 0) {
  		return array(
  			'CriteriasDocument.total_app >' => '50' 
  		);
  	} elseif(strcmp('dis', $fil) == 0) {
  		return array(
  			'CriteriasDocument.total_app <=' => '50' 
  		);
  	} elseif(strcmp('con', $fil) == 0) {
  		return array(
  			'CriteriasDocument.consenso >' => '50' 
  		);
  	} elseif(strcmp('don', $fil) == 0) {
  		return array(
  			'CriteriasDocument.consenso <=' => '50' 
  		);
  	} else { // all
  		return array(
  			'1' => '1' 
  		);
  	} 		
  }
}
?>
