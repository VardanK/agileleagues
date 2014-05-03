<?php

App::uses('AppController', 'Controller');

class TeamsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		if (!$this->isScrumMaster) {
			throw new ForbiddenException();
		} 
	}

	public function index() {
		$this->set('teams', $this->Team->find('all', array(
			'contain' => array(
				'ScrumMaster' => array('id', 'name'), 
				'ProductOwner' => array('id', 'name'), 
				'Developers' => array('id', 'name')
			)
		)));
	}

	private function _save($id = null) {
		$this->set('developers', $this->Player->freeDeveloperList());
		$this->set('scrumMasters', $this->Player->scrumMasterList());
		$this->set('productOwners', $this->Player->productOwnerList());
		
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Team->save($this->request->data)) {
				$this->flashSuccess(__('Team saved successfully.'));
				return $this->redirect('/teams');
			} else {
				$this->flashError(__('There are validation errors.'));
			}
		} else if ($id !== null) {
			$this->request->data = $this->Team->findById($id);
			if (!$this->request->data) {
				throw new NotFoundException();
			}
		}
	}

	public function add() {
		$this->_save();
	}

	public function edit($id) {
		$this->_save($id);
	}

	public function delete($id) {
		$team = $this->Team->find('first', array(
			'conditions' => array('Team.id' => $id),
			'contain' => array(
				'Developers' => array('id')
			)
		));
		if (!$team) {
			throw new NotFoundException();
		}
		if (count($team['Developers']) > 0) {
			$this->flashError('It is not possible to remove this team because it has some players assigned. Please remove  or unassign the players first. ');
		} else {
			if ($this->Team->delete($id)) {
				$this->flashSuccess(__('Team deleted successfully.'));
			} else {
				$this->flashError(__('Could not delete team.'));
			}
		}
		return $this->redirect('/teams');
	}
}