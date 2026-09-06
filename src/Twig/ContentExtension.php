<?php

namespace App\Twig;

use App\Repository\PageContentRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ContentExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private PageContentRepository $contentRepo
    ) {}

    public function getGlobals(): array
    {
        $allContents = $this->contentRepo->findAll();
        $contents = [];
        foreach ($allContents as $content) {
            $contents[$content->getSection()][$content->getKey()] = $content->getValue();
        }

        return [
            'contents' => $contents,
        ];
    }
}
