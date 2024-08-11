<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('content', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('publishedAt', TextType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/png, image/jpeg, image/jpg, image/webp',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2000k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/bpm',
                            'image/webp',
                        ],
                    ]),
                ],
            ]);

        $builder->get('publishedAt')->addModelTransformer(
            new CallbackTransformer(
                function (\DateTimeImmutable $publishedAt) {
                    return $publishedAt->format('d/m/Y');
                },
                function (string $publishedAt) {
                    return new \DateTimeImmutable($publishedAt);
                }
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
