<?php

namespace Sylphian\TrophyCategories\XF\Admin\Controller;

use Sylphian\TrophyCategories\Entity\Category;
use Sylphian\TrophyCategories\Repository\CategoryRepository;
use XF\ControllerPlugin\DeletePlugin;
use XF\Entity\Trophy;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class TrophyController extends XFCP_TrophyController
{
	public function actionIndex()
	{
		$reply = parent::actionIndex();

		if ($reply instanceof View)
		{
			$reply->setParam(
				'groupedTrophies',
				$this->getCategoryRepo()->groupTrophiesByCategory($reply->getParam('trophies') ?: [], true)
			);
		}

		return $reply;
	}

	protected function trophyAddEdit(Trophy $trophy)
	{
		$reply = parent::trophyAddEdit($trophy);

		if ($reply instanceof View)
		{
			$reply->setParam(
				'trophyCategories',
				$this->getCategoryRepo()->findCategoriesForList()->fetch()->pluckNamed('title', 'category_id')
			);
		}

		return $reply;
	}

	protected function trophySaveProcess(Trophy $trophy)
	{
		$form = parent::trophySaveProcess($trophy);

		$form->setupEntityInput($trophy, $this->filter([
			'sylphian_category_id' => 'uint',
		]));

		return $form;
	}

	protected function categoryAddEdit(Category $category): View
	{
		return $this->view(
			'Sylphian\TrophyCategories:Category\Edit',
			'sylphian_trophy_category_edit',
			['category' => $category]
		);
	}

	public function actionCategoryAdd()
	{
		return $this->categoryAddEdit($this->em()->create(Category::class));
	}

	public function actionCategoryEdit()
	{
		return $this->categoryAddEdit($this->assertCategoryExists($this->filter('category_id', 'uint')));
	}

	protected function categorySaveProcess(Category $category): FormAction
	{
		$form = $this->formAction();

		$form->basicEntitySave($category, $this->filter([
			'title' => 'str',
			'display_order' => 'uint',
		]));

		return $form;
	}

	public function actionCategorySave()
	{
		$this->assertPostOnly();

		$categoryId = $this->filter('category_id', 'uint');
		$category = $categoryId
			? $this->assertCategoryExists($categoryId)
			: $this->em()->create(Category::class);

		$this->categorySaveProcess($category)->run();

		return $this->redirect($this->buildLink('trophies'));
	}

	public function actionCategoryDelete()
	{
		$category = $this->assertCategoryExists($this->filter('category_id', 'uint'));
		$linkParams = ['category_id' => $category->category_id];

		/** @var DeletePlugin $plugin */
		$plugin = $this->plugin(DeletePlugin::class);
		return $plugin->actionDelete(
			$category,
			$this->buildLink('trophies/category-delete', null, $linkParams),
			$this->buildLink('trophies/category-edit', null, $linkParams),
			$this->buildLink('trophies'),
			$category->title
		);
	}

	/**
	 * @param int $id
	 * @param array|string|null $with
	 * @param string|null $phraseKey
	 *
	 * @return Category
	 */
	protected function assertCategoryExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists(Category::class, $id, $with, $phraseKey);
	}

	protected function getCategoryRepo(): CategoryRepository
	{
		return $this->repository(CategoryRepository::class);
	}
}
