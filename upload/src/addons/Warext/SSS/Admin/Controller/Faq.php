<?php

namespace Warext\SSS\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;
use Warext\SSS\Entity\Faq as FaqEntity;

class Faq extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('wrxtSssManage');
    }

    public function actionIndex()
    {
        $categoryId = $this->filter('category_id', 'uint');

        $finder = $this->finder('Warext\SSS:Faq')
            ->with('Category')
            ->order('category_id')
            ->order('display_order')
            ->order('faq_id');

        if ($categoryId)
        {
            $finder->where('category_id', $categoryId);
        }

        $categories = $this->finder('Warext\SSS:Category')
            ->order('display_order')
            ->order('title')
            ->fetch();

        return $this->view('Warext\SSS:FaqList', 'wrxt_sss_admin_faq_list', [
            'faqs' => $finder->fetch(),
            'categories' => $categories,
            'categoryId' => $categoryId
        ]);
    }

    public function actionAdd()
    {
        $faq = $this->em()->create('Warext\SSS:Faq');
        $categoryId = $this->filter('category_id', 'uint');
        if ($categoryId)
        {
            $faq->category_id = $categoryId;
        }

        return $this->faqAddEdit($faq);
    }

    public function actionEdit()
    {
        $faqId = $this->filter('faq_id', 'uint');
        return $this->faqAddEdit($this->assertFaqExists($faqId));
    }

    protected function faqAddEdit(FaqEntity $faq)
    {
        $categories = $this->finder('Warext\SSS:Category')
            ->order('display_order')
            ->order('title')
            ->fetch();

        if (!$categories->count())
        {
            return $this->error('Önce en az bir SSS kategorisi oluşturmalısınız.');
        }

        return $this->view('Warext\SSS:FaqEdit', 'wrxt_sss_admin_faq_edit', [
            'faq' => $faq,
            'categories' => $categories
        ]);
    }

    public function actionSave()
    {
        $this->assertPostOnly();
        $faqId = $this->filter('faq_id', 'uint');
        $faq = $faqId
            ? $this->assertFaqExists($faqId)
            : $this->em()->create('Warext\SSS:Faq');

        $input = $this->filter([
            'category_id' => 'uint',
            'question' => 'str',
            'answer' => 'str',
            'anchor' => 'str',
            'display_order' => 'uint',
            'is_active' => 'bool',
            'is_featured' => 'bool'
        ]);

        if (!$this->em()->find('Warext\SSS:Category', $input['category_id']))
        {
            return $this->error('Geçerli bir SSS kategorisi seçmelisiniz.');
        }

        $input['question'] = trim($input['question']);
        $input['answer'] = trim($input['answer']);
        $input['anchor'] = trim($input['anchor']);

        if ($input['question'] === '')
        {
            return $this->error('Soru alanı boş bırakılamaz.');
        }
        if ($input['answer'] === '')
        {
            return $this->error('Cevap alanı boş bırakılamaz.');
        }

        if ($input['anchor'] !== '' && !preg_match('/^[a-z0-9][a-z0-9-]{0,99}$/', $input['anchor']))
        {
            return $this->error('Doğrudan bağlantı anahtarı yalnızca küçük harf, sayı ve tire içerebilir.');
        }

        if ($input['anchor'] !== '')
        {
            $existing = $this->finder('Warext\SSS:Faq')
                ->where('anchor', $input['anchor'])
                ->where('faq_id', '<>', (int)$faq->faq_id)
                ->fetchOne();
            if ($existing)
            {
                return $this->error('Bu doğrudan bağlantı anahtarı başka bir SSS kaydında kullanılıyor.');
            }
        }

        $faq->bulkSet($input);
        $faq->save();

        return $this->redirect($this->buildLink('wrxt-sss-sorular'));
    }

    public function actionDelete()
    {
        $faqId = $this->filter('faq_id', 'uint');
        $faq = $this->assertFaqExists($faqId);

        if ($this->isPost())
        {
            $faq->delete();
            return $this->redirect($this->buildLink('wrxt-sss-sorular'));
        }

        return $this->view('Warext\SSS:FaqDelete', 'wrxt_sss_admin_faq_delete', [
            'faq' => $faq
        ]);
    }

    public function actionToggle()
    {
        $this->assertPostOnly();
        $faqId = $this->filter('faq_id', 'uint');
        $faq = $this->assertFaqExists($faqId);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return $this->redirect($this->buildLink('wrxt-sss-sorular'));
    }

    protected function assertFaqExists(int $id): FaqEntity
    {
        $faq = $this->em()->find('Warext\SSS:Faq', $id, ['Category']);
        if (!$faq)
        {
            throw $this->exception($this->notFound('İstenen SSS kaydı bulunamadı.'));
        }

        return $faq;
    }
}
