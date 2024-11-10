<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FileTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [
            FileType::class,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            'property',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (false === \array_key_exists('property', $options)) {
            return;
        }

        $parentData = $form->getParent()->getData();
        $accessor = PropertyAccess::createPropertyAccessor();
        $value = $accessor->getValue($parentData, $options['property']);

        $partsOfClassName = \explode('\\', \get_class($parentData));
        $entityName = \strtolower(\array_pop($partsOfClassName));

        if (\strlen($value) > 0) {
            $view->vars['src'] = '/uploads/' . $entityName . '/' . $value;
        } else {
            $view->vars['src'] = '';
        }
    }
}
