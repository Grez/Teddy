<?php

namespace Teddy\GameModule\Presenters;

use Nette\Utils\ArrayHash;
use Teddy\Entities\PM\Message;
use Teddy\Entities\User\MessagesQuery;
use Teddy\Forms\Form;
use Teddy\Entities\PM\Messages;



/**
 * @TODO: Get conversation
 */
class MessagesPresenter extends BasePresenter
{

	/**
	 * @var Messages
	 * @inject
	 */
	public $msgsRepository;

	/**
	 * @var Message[]
	 */
	protected $msgs = [];



	public function renderDefault()
	{
		$query = (new MessagesQuery())
			->onlyNotDeletedByRecipient()
			->onlyReadableBy($this->user);
		$msgs = $this->msgsRepository->fetch($query);
		$msgs->applyPaginator($this['visualPaginator']->getPaginator(), 20);
		$this->template->msgs = $msgs;
	}



	/**
	 * @param string|NULL $id nick
	 */
	public function actionNew($id = NULL)
	{
		if ($id) {
			$this['newMsgForm']['to']->setValue($id)->setAttribute('readonly', 'readonly');
		}
	}



	/**
	 * @param int $id
	 */
	public function renderDetail($id)
	{
		/** @var Message $msg */
		$msg = $this->msgsRepository->find($id);
		if (!$msg || !$msg->isReadableByUser($this->user)) {
			$this->warningFlashMessage('This message doesn\'t exist or wasn\'t intended for you.');
			$this->redirect('default');
		}

		$msg->setUnread(FALSE);
		$this->em->flush();

		$defaults = [
			'to' => $msg->getFrom()->getNick(),
			'subject' => $msg->getSubject(),
			'conversation' => $msg->getConversationId(),
		];

		$this['newMsgForm']['to']->setAttribute('readonly', 'readonly');
		$this['newMsgForm']['subject']->setAttribute('readonly', 'readonly');
		$this['newMsgForm']->setDefaults($defaults);
		$this->template->msg = $msg;
	}



	/**
	 * @param int $id
	 */
	public function handleDelete($id)
	{
		/** @var Message $msg */
		$msg = $this->msgsRepository->find($id);
		if (!$msg || !$msg->isReadableByUser($this->user)) {
			$this->warningFlashMessage('This message doesn\'t exist or wasn\'t intended for you.');
			$this->redirect('default');
		}

		$msg->deleteBy($this->user);
		$this->em->flush();

		$this->successFlashMessage('Message has been deleted');
		$this->redirect('default');
	}



	/**
	 * @return Form
	 */
	public function createComponentNewMsgForm()
	{
		$form = new Form();
		$form->addHidden('conversation');
		$form->addText('to', 'Recipient')
			->setRequired();
		$form->addText('subject', 'Předmět')
			->setRequired();
		$form->addTextarea('text', 'Message')
			->setRequired();
		$form->addSubmit('send', 'Submit');
		$form->onSuccess[] = $this->newMsgFormSuccess;
		return $form->setBootstrapRenderer();
	}



	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function newMsgFormSuccess(Form $form, ArrayHash $values)
	{
		$recipient = $this->users->getByNick($values->to);
		if (!$recipient) {
			$this->warningFlashMessage('This user doesn\'t exist.');
			$this->redirect('this');
		}

		$this->msgsRepository->createMessage($recipient, $this->user, $values->subject, $values->text, $values->conversation);
		$this->em->flush();

		$this->successFlashMessage('Message sent');
		$this->redirect('default');
	}

}
