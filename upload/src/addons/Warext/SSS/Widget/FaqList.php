<?php

namespace Warext\SSS\Widget;

use XF\Http\Request;
use XF\Widget\AbstractWidget;
use XF\Widget\WidgetRenderer;

class FaqList extends AbstractWidget
{
    protected $defaultOptions = [
        'limit' => 5,
        'featured_only' => false
    ];

    public function render(): string|WidgetRenderer
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasPermission('wrxtSss', 'view'))
        {
            return '';
        }

        $limit = max(1, min(20, (int)$this->options['limit']));
        $finder = $this->repository('Warext\SSS:Faq')->findActiveFaqsForList()->limit(100);
        if (!empty($this->options['featured_only']))
        {
            $finder->where('is_featured', 1);
        }

        $faqs = [];
        foreach ($finder->fetch() as $faq)
        {
            if (!$faq->Category || !$faq->Category->canViewFor($visitor) || !$faq->canViewFor($visitor))
            {
                continue;
            }
            $faqs[] = $faq;
            if (count($faqs) >= $limit) { break; }
        }

        if (!$faqs) { return ''; }
        return $this->renderer('wrxt_sss_widget', ['faqs' => $faqs]);
    }

    public function verifyOptions(Request $request, array &$options, &$error = null)
    {
        $options = $request->filter([
            'limit' => 'uint',
            'featured_only' => 'bool'
        ]);
        $options['limit'] = max(1, min(20, (int)$options['limit']));
        return true;
    }
}
