<?php

App::uses('AppController', 'Controller');

class PlayersController extends AppController {

	public $uses = array('Team');
	public $components = array('Email', 'Utils');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('login', 'logout', 'join', 'signup', 'signin');
	}

	public function index() {
		$this->set('players', $this->Player->allFromPlayerTeam($this->Auth->user('id')));
	}

	public function signup() {
		$this->layout = 'institutional';
		$this->set('title_for_layout', 'Sign Up');

		if ($this->request->is('post') || $this->request->is('put')) {
			// Ignore repeat password validation rule
			unset($this->Player->validate['repeat_password']);

			if ($this->Player->save($this->request->data)) {

				$name = $this->request->data['Player']['name'];
				$email = $this->request->data['Player']['email'];
				$hash = $this->Utils->verificationHash($this->Player->id);

				$this->Email->template(
					'signup', array(
						'name' => $name,
						'hash' => $hash
					)
				);
				$this->Email->subject(__('%s, Welcome to Agile Leagues', $name));
				$this->Email->send($email);
				
				// Update the player record with the hash
				$this->Player->save(array(
					'Player' => array(
						'id' => $this->Player->id,
						'verification_hash' => $hash
					)
				));

				$this->flashSuccess(__('Account created successfully! A verification email message was sent to your address: %s.', $email));
				return $this->redirect('/');
			} else {
				$this->flashError(__('Please check the fields below, there are validation errors :('));
			}
		}

		$this->set('playerTypes', array(PLAYER_TYPE_SCRUMMASTER => 'ScrumMaster'));
	}

	private function verify($player) {
		$player['Player']['verified_in'] = date('Y-m-d H:i:s');
		if ($this->Player->save($player)) {
			$this->flashSuccess(__('Account verified successfully!'));
			$player = $this->Player->findById($player['Player']['id']);
			$this->Auth->login($player['Player']);
			return $this->redirect($this->Auth->redirectUrl());
		} else {
			$this->flashError(__('There are validation errors.'));
		}
	}

	public function join($hash = null) {
		$this->set('title_for_layout', 'Join');

		if ($hash === null || empty($hash)) {
			throw new NotFoundException();
		}

		$player = $this->Player->findByVerificationHash($hash);

		if (!$player) {
			throw new NotFoundException();
		} else if ($player['Player']['verified_in']) {
			$this->flashError(__('This account has already been verified.'));
		} else {
			$id = $player['Player']['id'];

			// If the password is already defined, proceed with instant verification
			if ($player['Player']['password']) {
				$this->verify(array(
					'Player' => array(
						'id' => $player['Player']['id']
					)
				));
			}

			if ($this->request->is('post') || $this->request->is('put')) {
				$player = $this->request->data;
				$this->verify($player);
			} else {
				$this->request->data = array(
					'Player' => array('id' => $id)
				);
			}
		}
	}

	public function invite() {
		$this->set('title_for_layout', 'Invite');

		if (!$this->isScrumMaster) {
			throw new ForbiddenException();
		}

		if ($this->request->is('post') || $this->request->is('put')) {
			// Ignore password validation rules because the password is set after the account is verified
			unset($this->Player->validate['password']);
			unset($this->Player->validate['repeat_password']);

			$this->Player->validate['team_id'] = 'notEmpty';

			if ($this->request->data['Player']['player_type_id'] == PLAYER_TYPE_SCRUMMASTER) {
				$this->flashError(__('You cannot create other ScrumMasters.'));
			} else if ($this->Player->save($this->request->data)) {

				$email = $this->request->data['Player']['email'];
				$team = $this->Team->findById($this->request->data['Player']['team_id']);
				$scrumMasterName = $this->Auth->user('name');
				$teamName = $team['Team']['name'];
				
				$hash = $this->Utils->verificationHash($this->Player->id);

				$this->Email->template(
					'scrummaster_invitation', array(
						'scrumMasterName' => $scrumMasterName, 
						'teamName' => $teamName,
						'hash' => $hash
					)
				);
				$this->Email->subject(__('%s invited you to join %s on Agile Leagues', $scrumMasterName, $teamName));
				$this->Email->send($email);
				
				// Update the player record with the hash
				$this->Player->save(array(
					'Player' => array(
						'id' => $this->Player->id,
						'verification_hash' => $hash
					)
				));

				$this->flashSuccess(__('Player invited successfully! An account verification email message was sent to %s.', $email));
				return $this->redirect('/players');
			} else {
				$this->flashError(__('There are validation errors.'));
			}
		}

		$this->set('playerTypes', array(
			PLAYER_TYPE_DEVELOPER => 'Developer',
			PLAYER_TYPE_PRODUCT_OWNER => 'Product Owner'
		));
		$this->set('teams', $this->Team->simpleFromScrumMaster($this->Auth->user('id')));
	}

	public function signin() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				return $this->redirect($this->Auth->redirectUrl());
			} else {
				$this->flashError(__('Invalid email and/or password.'));
			}
		}
		return $this->redirect('/pages/home/');
	}

	public function login(){
		$this->set('title_for_layout', 'Login');

		if ($this->Auth->user() != null) {
			return $this->redirect($this->Auth->redirectUrl());
		}
		if ($this->request->is('post')) {
			// Login por AJAX
			if ($this->Auth->login()) {
				$this->set('login_status', 'success');
			} else {
				$this->set('login_status', 'invalid');
			}
			$this->set('_serialize', array('login_status'));
			$this->layout = 'ajax';
		} else {
			$this->layout = 'login';
		}
	}

	public function logout() {
		$this->redirect($this->Auth->logout());
	}

	// Change team
	public function team($id) {
		$this->set('title_for_layout', 'Change Team');

		$this->set('teams', $this->Team->simple());

		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Player->save($this->request->data)) {
				$this->flashSuccess(__('Player saved successfully!'));
				return $this->redirect('/players');
			} 
			else {
				//@codeCoverageIgnoreStart
				$this->flashError(__('Error while trying to save player.'));
				// @codeCoverageIgnoreEnd
			}
		} else {
			$this->request->data = $this->Player->findById($id);
			if (!$this->request->data) {
				throw new NotFoundException();
			}
		}
	}

	public function myaccount() {
		$this->set('title_for_layout', 'Account');

		if ($this->request->is('get')) {
			$this->request->data = $this->player;
		} else {
			if ($this->Player->save($this->request->data)) {
				unset ($this->request->data['Player']['password']);
				unset ($this->request->data['Player']['repeat_password']);
				$this->flashSuccess(__('Data updated successfully!'));
			} else {
				$this->flashError(__('Error while trying to edit your data :('));
			}
		}
	}
}