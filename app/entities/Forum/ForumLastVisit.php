<?php

namespace Teddy\Entities\Forum;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;
use Teddy\Entities;
use Doctrine\ORM\Mapping as ORM;
use Teddy\Entities\User\User;



/**
 * @ORM\Entity()
 */
class ForumLastVisit extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Teddy\Entities\User\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 * @var User
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Forum", inversedBy="lastVisits")
	 * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
	 * @var Forum
	 */
	protected $forum;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $lastVisitAt;



	public function __construct(User $user, Forum $forum)
	{
		$this->user = $user;
		$this->forum = $forum;
		$this->lastVisitAt = new \DateTime();
	}



	/**
	 * @return ForumLastVisit
	 */
	public function updateLastVisitAt()
	{
		$this->lastVisitAt = new \DateTime();
		return $this;
	}



	/**
	 * @return \DateTime
	 */
	public function getLastVisitAt()
	{
		return $this->lastVisitAt;
	}

}
