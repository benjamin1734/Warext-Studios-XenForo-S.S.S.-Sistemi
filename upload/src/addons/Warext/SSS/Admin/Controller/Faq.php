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
        $finder = $this->finder('Warext\SSS:Faq')->with('Category')
            ->order('category_id')->order('display_order')->order('faq_id');
        if ($categoryId) { $finder->where('category_id', $categoryId); }

        return $this->view('Warext\SSS:FaqList', 'wrxt_sss_admin_faq_list', [
            'faqs' => $finder->fetch(), 'categories' => $this->getCategories(), 'categoryId' => $categoryId
        ]);
    }

    public function actionAdd()
    {
        $faq = $this->em()->create('Warext\SSS:Faq');
        $categoryId = $this->filter('category_id', 'uint');
        if ($categoryId) { $faq->category_id = $categoryId; }
        return $this->faqAddEdit($faq);
    }

    public function actionEdit()
    {
        return $this->faqAddEdit($this->assertFaqExists($this->filter('faq_id', 'uint')));
    }

    protected function faqAddEdit(FaqEntity $faq)
    {
        $categories = $this->getCategories();
        if (!$categories->count()) { return $this->error('Önce en az bir SSS kategorisi oluşturmalısınız.'); }

        return $this->view('Warext\SSS:FaqEdit', 'wrxt_sss_admin_faq_edit', [
            'faq' => $faq,
            'categories' => $categories,
            'userGroups' => $this->finder('XF:UserGroup')->order('title')->fetch(),
            'allowedGroupIds' => $this->parseGroupIds($faq->allowed_user_group_ids)
        ]);
    }

    public function actionSave()
    {
        $this->assertPostOnly();
        $faqId = $this->filter('faq_id', 'uint');
        $faq = $faqId ? $this->assertFaqExists($faqId) : $this->em()->create('Warext\SSS:Faq');
        $input = $this->filter([
            'category_id' => 'uint', 'question' => 'str', 'answer' => 'str', 'anchor' => 'str',
            'display_order' => 'uint', 'is_active' => 'bool', 'is_featured' => 'bool'
        ]);
        $groups = array_values(array_unique(array_filter($this->filter('allowed_user_group_ids', 'array-uint'))));
        if (!$this->em()->find('Warext\SSS:Category', $input['category_id'])) { return $this->error('Geçerli bir SSS kategorisi seçmelisiniz.'); }

        $input['question'] = trim($input['question']);
        $input['answer'] = trim($input['answer']);
        $input['anchor'] = trim($input['anchor']);
        $input['allowed_user_group_ids'] = implode(',', $groups);
        if ($input['question'] === '') { return $this->error('Soru alanı boş bırakılamaz.'); }
        if ($input['answer'] === '') { return $this->error('Cevap alanı boş bırakılamaz.'); }
        if ($input['anchor'] !== '' && !preg_match('/^[a-z0-9][a-z0-9-]{0,99}$/', $input['anchor']))
        { return $this->error('Doğrudan bağlantı anahtarı yalnızca küçük harf, sayı ve tire içerebilir.'); }
        if ($input['anchor'] !== '')
        {
            $existing = $this->finder('Warext\SSS:Faq')->where('anchor', $input['anchor'])
                ->where('faq_id', '<>', (int)$faq->faq_id)->fetchOne();
            if ($existing) { return $this->error('Bu doğrudan bağlantı anahtarı başka bir SSS kaydında kullanılıyor.'); }
        }
        $faq->bulkSet($input); $faq->save();
        return $this->redirect($this->buildLink('wrxt-sss-sorular'));
    }

    public function actionDelete()
    {
        $faq = $this->assertFaqExists($this->filter('faq_id', 'uint'));
        if ($this->isPost()) { $faq->delete(); return $this->redirect($this->buildLink('wrxt-sss-sorular')); }
        return $this->view('Warext\SSS:FaqDelete', 'wrxt_sss_admin_faq_delete', ['faq' => $faq]);
    }

    public function actionToggle()
    {
        $this->assertPostOnly();
        $faq = $this->assertFaqExists($this->filter('faq_id', 'uint'));
        $faq->is_active = !$faq->is_active; $faq->save();
        return $this->redirect($this->buildLink('wrxt-sss-sorular'));
    }

    public function actionSort()
    {
        $categories = $this->getCategories();
        if (!$categories->count()) { return $this->error('Sıralanacak bir SSS kategorisi bulunmuyor.'); }
        $categoryId = $this->filter('category_id', 'uint');
        if (!$categoryId) { foreach ($categories as $category) { $categoryId = (int)$category->category_id; break; } }
        $faqs = $this->finder('Warext\SSS:Faq')->where('category_id', $categoryId)
            ->order('display_order')->order('faq_id')->fetch();
        return $this->view('Warext\SSS:FaqSort', 'wrxt_sss_admin_faq_sort', [
            'categories' => $categories, 'categoryId' => $categoryId, 'faqs' => $faqs
        ]);
    }

    public function actionSortSave()
    {
        $this->assertPostOnly();
        $categoryId = $this->filter('category_id', 'uint');
        $order = array_values(array_unique($this->filter('order', 'array-uint')));
        $position = 10;
        foreach ($order as $faqId)
        {
            $faq = $this->em()->find('Warext\SSS:Faq', $faqId);
            if (!$faq || (int)$faq->category_id !== $categoryId) { continue; }
            $faq->display_order = $position; $faq->save(); $position += 10;
        }
        return $this->redirect($this->buildLink('wrxt-sss-sorular', null, ['category_id' => $categoryId]));
    }

    protected function getCategories()
    {
        return $this->finder('Warext\SSS:Category')->order('display_order')->order('title')->fetch();
    }

    protected function parseGroupIds(string $ids): array
    {
        return $ids === '' ? [] : array_values(array_unique(array_filter(array_map('intval', explode(',', $ids)))));
    }

    protected function assertFaqExists(int $id): FaqEntity
    {
        $faq = $this->em()->find('Warext\SSS:Faq', $id, ['Category']);
        if (!$faq) { throw $this->exception($this->notFound('İstenen SSS kaydı bulunamadı.')); }
        return $faq;
    }
}
