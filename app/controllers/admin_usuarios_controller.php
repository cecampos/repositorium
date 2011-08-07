<?php
/**
 * admin_usuarios_controller.php
 * 
 * Controlador de administracion de usuarios
 * 
 * @package   controllers
 * @author    mquezada <mquezada@dcc.uchile.cl>
 * @copyright Copyright (c) 2011 
 */

class AdminUsuariosController extends AppController {
  var $uses = array('User','Expert');
  var $paginate = array(
	  'User' => array(
		'limit' => '15',
		'order' => array(
		  'User.created' => 'asc'
		),
		'recursive' => -1,
  ));

  function beforeFilter() {
	if(!$this->Session->check('User.esAdmin')) {
	  $this->Session->setFlash('You don\'t have permission to access this page');
	  $this->redirect(array('controller' => 'pages'));
	}
  }

  function index() {
	$this->redirect('listar');
  }

  function listar() {
	$debug = $data = $this->paginate('User');
	$experts = $this->Expert->find('all', array('recursive' => -1));
	$current = 'usuarios';
	$this->set(compact('data', 'current', 'debug', 'experts'));
  }

  function add() {
  	$this->set('current', 'usuarios');	
	if (!empty($this->data)) {
	  if ($this->User->save($this->data)) {
		$this->Session->setFlash('User added successfully');		
		$this->redirect('listar');
	  } else {
		$this->Session->setFlash($this->User->invalidFields(), 'flash_errors');		
	  }
	}
  }

  function remove($id = null) {
	if (!is_null($id)) {
	  if($this->User->delete($id)) {
		$this->Session->setFlash('User '.$id.' deleted');
		CakeLog::write('activity','User '.$id.' deleted' );
	  } else {
		$this->Session->setFlash('There was an error trying to remove that user');
	  }
	}
   	$this->redirect($this->referer());	
  }

  function edit($id = null) {
  	$this->set('current', 'usuarios');	
	$this->User->id = $id;
	if (empty($this->data)) {
	  $this->data = $this->User->read();
	} else {
	  // password validation
	  if(!empty($this->data['User']['tmp_password'])) {
		$p1 = $this->data['User']['tmp_password'];
		$p2 = $this->data['User']['tmp_password2'];
		if(strcmp($p1,$p2) == 0) {
		  $this->data['User']['password'] = $p1;
		} else {
		  $this->Session->setFlash('Passwords don\'t match');
		  $this->redirect(array('action' => 'edit', $id));
		}
	  }
	    
	  $this->User->set($this->data);
	  if ($this->User->validates()) {
		// saves edited data
		if($this->User->save($this->data)) {
		  $this->Session->setFlash('The user was modified');
		  CakeLog::write('activity', 'User '.$id.' was modified');
		  $this->redirect('index');
		}
	  } else {
		$this->Session->setFlash($this->User->invalidFields(),'flash_errors');
		$this->redirect(array('action' => 'edit', $id));
	  }
	}
	if(is_null($id))
	  $this->redirect('add');
	 
  }  
}
?>
