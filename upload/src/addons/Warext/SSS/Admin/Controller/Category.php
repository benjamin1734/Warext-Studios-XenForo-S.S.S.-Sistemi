<?php

namespace Warext\SSS\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;
use Warext\SSS\Entity\Category as CategoryEntity;

class Category extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('wrxtSssManage');
    }

    public function actionIndex()
    {
        $categories = $this->finder('Warext\SSS:Category')
            ->order('display_order')
            ->order('title')
            ->fetch();

        $faqCounts = $this->db()->fetchPairs(
            'SELECT category_id, COUNT(*) FROM xf_wrxt_sss_faq GROUP BY category_id'
        );

        return $this->view('Warext\SSS:CategoryList', 'wrxt_sss_admin_category_list', [
            'categories' => $categories,
            'faqCounts' => $faqCounts
        ]);
    }

    public function actionAdd()
    {
        return $this->categoryAddEdit($this->em()->create('Warext\SSS:Category'));
    }

    public function actionEdit()
    {
        $categoryId = $this->filter('category_id', 'uint');
        return $this->categoryAddEdit($this->assertCategoryExists($categoryId));
    }

    protected function categoryAddEdit(CategoryEntity $category)
    {
        return $this->view('Warext\SSS:CategoryEdit', 'wrxt_sss_admin_category_edit', [
            'category' => $category
        ]);
    }

    public function actionSave()
    {
        $this->assertPostOnly();
        $categoryId = $this->filter('category_id', 'uint');
        $category = $categoryId
            ? $this->assertCategoryExists($categoryId)
            : $this->em()->create('Warext\SSS:Category');

        $input = $this->filter([
            'title' => 'str',
            'description' => 'str',
            'icon' => 'str',
            'display_order' => 'uint',
            'is_active' => 'bool'
        ]);

        $input['title'] = trim($input['title']);
        $input['description'] = trim($input['description']);
        $input['icon'] = trim($input['icon']);

        if ($input['title'] === '')
        {
            return $this->error('Kategori adı boş bırakılamaz.');
        }

        $category->bulkSet($input);
        $category->save();

        return $this->redirect($this->buildLink('wrxt-sss-kategoriler'));
    }

    public function actionDelete()
    {
        $categoryId = $this->filter('category_id', 'uint');
        $category = $this->assertCategoryExists($categoryId);

        if ($this->isPost())
        {
            $this->db()->beginTransaction();
            try
            {
                $this->db()->delete('xf_wrxt_sss_faq', 'category_id = ?', $category->category_id);
                $category->delete();
                $this->db()->commit();
            }
            catch (\Throwable $e)
            {
                $this->db()->rollback();
                throw $e;
            }

            return $this->redirect($this->buildLink('wrxt-sss-kategoriler'));
        }

        $faqCount = $this->finder('Warext\SSS:Faq')
            ->where('category_id', $category->category_id)
            ->total();

        return $this->view('Warext\SSS:CategoryDelete', 'wrxt_sss_admin_category_delete', [
            'category' => $category,
            'faqCount' => $faqCount
        ]);
    }

    public function actionToggle()
    {
        $this->assertPostOnly();
        $categoryId = $this->filter('category_id', 'uint');
        $category = $this->assertCategoryExists($categoryId);
        $category->is_active = !$category->is_active;
        $category->save();

        return $this->redirect($this->buildLink('wrxt-sss-kategoriler'));
    }

    protected function assertCategoryExists(int $id): CategoryEntity
    {
        $category = $this->em()->find('Warext\SSS:Category', $id);
        if (!$category)
        {
            throw $this->exception($this->notFound('İstenen SSS kategorisi bulunamadı.'));
        }

        return $category;
    }
}
