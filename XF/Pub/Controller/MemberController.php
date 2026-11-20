<?php

namespace Sylphian\TrophyCategories\XF\Pub\Controller;

use Sylphian\TrophyCategories\Repository\CategoryRepository;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;

class MemberController extends XFCP_MemberController
{
	public function actionAbout(ParameterBag $params)
	{
		return $this->addGroupedTrophies(parent::actionAbout($params));
	}

	public function actionTrophies(ParameterBag $params)
	{
		return $this->addGroupedTrophies(parent::actionTrophies($params));
	}

	protected function addGroupedTrophies(AbstractReply $reply): AbstractReply
	{
		if ($reply instanceof View && $reply->getParam('trophies'))
		{
			$reply->setParam(
				'groupedTrophies',
				$this->repository(CategoryRepository::class)->groupTrophiesByCategory($reply->getParam('trophies'))
			);
		}

		return $reply;
	}
}
