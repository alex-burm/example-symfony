<?php

namespace App\Twig;

use App\Entity\ContentPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ContentPageExtension extends AbstractExtension
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security $security,
        protected Environment $twig,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'getContentPage',
                [$this, 'getContentPage'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    public function getContentPage(string $name): string
    {
        static $list = null;

        if (\is_null($list)) {
            $list = $this->entityManager->getRepository(ContentPage::class)->findAll();
        }

        $records = \array_filter(
            $list,
            static fn (ContentPage $x) => $x->getName() === $name
        );

        if (\count($records) === 0) {
            return new Response;
        }

        $record = \current($records);

        if (\is_null($this->security->getUser())) {
            return $record->getValue();
        }

        return $this->twig->render('shared/contentPage.html.twig', [
            'record' => $record,
        ]);
    }
}
