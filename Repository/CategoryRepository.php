<?php

namespace Sylphian\TrophyCategories\Repository;

use Sylphian\TrophyCategories\Entity\Category;
use XF\Entity\Trophy;
use XF\Entity\UserTrophy;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class CategoryRepository extends Repository
{
	public function findCategoriesForList(): Finder
	{
		return $this->finder(Category::class)->order(['display_order', 'title']);
	}

	/**
	 * Groups trophies, or awarded user trophies, under their categories in display order.
	 * Uncategorised items come last under key 0. Categories with no items are left out unless $includeEmpty is set.
	 *
	 * @param iterable<Trophy|UserTrophy> $items
	 *
	 * @return array<int, array{category: Category|null, items: list<Trophy|UserTrophy>}>
	 */
	public function groupTrophiesByCategory(iterable $items, bool $includeEmpty = false): array
	{
		$grouped = [];
		foreach ($this->findCategoriesForList()->fetch() AS $category)
		{
			$grouped[$category->category_id] = [
				'category' => $category,
				'items' => [],
			];
		}

		$uncategorised = [];
		foreach ($items AS $item)
		{
			/** @var \Sylphian\TrophyCategories\XF\Entity\Trophy|null $trophy */
			$trophy = $item instanceof UserTrophy ? $item->Trophy : $item;
			$categoryId = $trophy ? $trophy->sylphian_category_id : 0;

			if ($categoryId && isset($grouped[$categoryId]))
			{
				$grouped[$categoryId]['items'][] = $item;
			}
			else
			{
				$uncategorised[] = $item;
			}
		}

		if (!$includeEmpty)
		{
			$grouped = array_filter($grouped, function (array $group): bool
			{
				return !empty($group['items']);
			});
		}

		if ($uncategorised)
		{
			$grouped[0] = [
				'category' => null,
				'items' => $uncategorised,
			];
		}

		return $grouped;
	}
}
