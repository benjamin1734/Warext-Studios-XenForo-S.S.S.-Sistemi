<?php

namespace Warext\SSS\Pub\Controller;

use XF\Pub\Controller\AbstractController;

class Sss extends AbstractController
{
    public function actionIndex()
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasPermission('wrxtSss', 'view'))
        {
            return $this->noPermission();
        }

        $categories = $this->repository('Warext\SSS:Category')->findActiveCategoriesForList()->fetch();
        $faqs = $this->repository('Warext\SSS:Faq')->findActiveFaqsForList()->fetch();

        $faqsByCategory = [];
        foreach ($faqs as $faq)
        {
            if (!$faq->canViewFor($visitor) || !$faq->Category || !$faq->Category->canViewFor($visitor))
            {
                continue;
            }
            $faqsByCategory[(int)$faq->category_id][] = $faq;
        }

        $groups = [];
        foreach ($categories as $category)
        {
            if (!$category->canViewFor($visitor)) { continue; }
            $categoryFaqs = $faqsByCategory[(int)$category->category_id] ?? [];
            if (!$categoryFaqs) { continue; }
            $groups[] = ['category' => $category, 'faqs' => $categoryFaqs];
        }

        $options = \XF::options();
        $pageTitle = trim((string)$options->wrxtSssPageTitle);
        $pageDescription = trim((string)$options->wrxtSssPageDescription);
        $badgeDays = max(0, (int)$options->wrxtSssBadgeDays);
        if ($pageTitle === '') { $pageTitle = (string)\XF::phrase('wrxt_sss_title'); }
        if ($pageDescription === '') { $pageDescription = (string)\XF::phrase('wrxt_sss_description'); }

        return $this->view('Warext\SSS:SssIndex', 'wrxt_sss_index', [
            'groups' => $groups,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'badgeCutoff' => $badgeDays > 0 ? \XF::$time - ($badgeDays * 86400) : 0
        ]);
    }
}
