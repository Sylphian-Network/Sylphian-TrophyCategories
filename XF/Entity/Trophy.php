<?php

namespace Sylphian\TrophyCategories\XF\Entity;

use Sylphian\TrophyCategories\Entity\Category;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $sylphian_category_id
 *
 * RELATIONS
 * @property-read Category|null $SylphianCategory
 */
class Trophy extends XFCP_Trophy
{
	protected function verifySylphianCategoryId(&$categoryId)
	{
		if ($categoryId > 0)
		{
			$category = $this->_em->find(Category::class, $categoryId);

			if (!$category)
			{
				$this->error(\XF::phrase('sylphian_trophy_please_select_valid_category'), 'sylphian_category_id');
				return false;
			}
		}

		return true;
	}

	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);

		$structure->columns['sylphian_category_id'] = ['type' => Entity::UINT, 'default' => 0];

		$structure->relations['SylphianCategory'] = [
			'entity' => 'Sylphian\TrophyCategories:Category',
			'type' => Entity::TO_ONE,
			'conditions' => [
				['category_id', '=', '$sylphian_category_id'],
			],
			'primary' => true,
		];

		return $structure;
	}
}
