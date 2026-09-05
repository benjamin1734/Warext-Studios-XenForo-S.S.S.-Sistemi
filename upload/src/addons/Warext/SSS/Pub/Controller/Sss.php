<?php

namespace Warext\SSS\Pub\Controller;

use XF\Pub\Controller\AbstractController;

class Sss extends AbstractController
{
    public function actionIndex()
    {
        if (!\XF::visitor()->hasPermission('wrxtSss', 'view'))
        {
            return $this->noPermission();
        }

        $categories = $this->repository('Warext\SSS:Category')
            ->findActiveCategoriesForList()
            ->fetch();

        $faqs = $this->repository('Warext\SSS:Faq')
            ->findActiveFaqsForList()
            ->fetch();

        $faqsByCategory = [];
        foreach ($faqs as $faq)
        {
            $faqsByCategory[(int)$faq->category_id][] = $faq;
        }

        $groups = [];
        foreach ($categories as $category)
        {
            $categoryFaqs = $faqsByCategory[(int)$category->category_id] ?? [];
            if (!$categoryFaqs)
            {
                continue;
            }

            $groups[] = [
                'category' => $category,
                'faqs' => $categoryFaqs
            ];
        }

        return $this->view(
            'Warext\SSS:SssIndex',
            'wrxt_sss_index',
            ['groups' => $groups]
        );
    }
}
