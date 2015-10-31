<?php

namespace Teddy\AdminModule\Presenters;

use Nette;
use Teddy;
use Teddy\Entities\User\User;
use Teddy\Entities\Logs\UserLogs;



class BasePresenter extends Teddy\Presenters\BasePresenter
{

	/** @var User */
	protected $user;

	/** @var UserLogs @inject */
	public $userLogs;

	/** @var array */
	protected $sections = [
		'Main' => 'Main',
		'Stats' => 'Stats',
		'Users' => 'Users',
		'Admins' => 'Admins',
		'Bans' => 'Bans',
		'Game' => 'Game',
		'Antimulti' => 'Antimulti',
	];



	/**
	 * Is logged, is admin, is allowed?
	 */
	protected function startup()
	{
		parent::startup();

		$user = $this->getUser();
		if (!$user->isLoggedIn()) {
			$this->flashMessage(_('You are not logged in'), 'error');
			$this->redirect(':Index:Homepage:default');
		}

		$this->user = $this->users->find($user->id);
		if (!$this->user->isAdmin()) {
			$this->flashMessage(_('You are not admin'), 'error');
			$this->redirect(':Index:Homepage:default');
		}

		if (!$this->user->isAllowed($this->presenter->getName())) {
			$this->flashMessage(_('You are not allowed here'), 'error');
			$this->redirect(':Admin:Main:default');
		}

		$this->template->user = $this->user;
		$this->template->sections = $this->sections;
	}



	/**
	 * @return \WebLoader\Nette\CssLoader
	 */
	protected function createComponentCssAdmin()
	{
		return $this->webLoader->createCssLoader('admin');
	}



	/**
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	protected function createComponentJsAdmin()
	{
		return $this->webLoader->createJavaScriptLoader('admin');
	}

}
