<?php

namespace Sylphian\TrophyCategories\XF\Pub\Controller;

use Sylphian\TrophyCategories\Repository\CategoryRepository;
use XF\Mvc\Reply\View;

class HelpController extends XFCP_HelpController
{
	protected function handleHelpPage($pageName)
	{
		$reply = parent::handleHelpPage($pageName);

		// The trophies help page is filled by a static callback, which class extensions cannot reach.
		if ($reply instanceof View && $pageName === 'trophies' && $reply->getParam('trophies'))
		{
			$reply->setParam(
				'groupedTrophies',
				$this->repository(CategoryRepository::class)->groupTrophiesByCategory($reply->getParam('trophies'))
			);
		}

		return $reply;
	}
}
