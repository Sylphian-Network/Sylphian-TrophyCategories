<?php

namespace Sylphian\TrophyCategories\Entity;

use XF\Entity\Trophy;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null $category_id
 * @property string $title
 * @property int $display_order
 *
 * RELATIONS
 * @property-read AbstractCollection<Trophy> $Trophies
 */
class Category extends Entity
{
	public static function getStructure(Structure $structure): Structure
	{
		$structure->table = 'xf_sylphian_trophy_category';
		$structure->shortName = 'Sylphian\TrophyCategories:Category';
		$structure->primaryKey = 'category_id';
		$structure->columns = [
			'category_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
			'title' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
			'display_order' => ['type' => self::UINT, 'default' => 1],
		];
		$structure->relations = [
			'Trophies' => [
				'entity' => 'XF:Trophy',
				'type' => self::TO_MANY,
				'conditions' => [
					['sylphian_category_id', '=', '$category_id'],
				],
				'key' => 'trophy_id',
			],
		];

		return $structure;
	}

	protected function _postDelete(): void
	{
		$this->db()->update('xf_trophy', [
			'sylphian_category_id' => 0,
		], 'sylphian_category_id = ?', $this->category_id);
	}
}
