<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    ) {
    }

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
            ->add('tags', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/png, image/jpeg, image/jpg, image/webp',
                ],
                'property' => 'image',
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
                function (?\DateTimeImmutable $publishedAt) {
                    return $publishedAt?->format('d/m/Y');
                },
                function (string $publishedAt) {
                    return new \DateTimeImmutable($publishedAt);
                }
            )
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $post = $event->getData();
            $form = $event->getForm();

            $names = \array_map(trim(...), \explode(',', $form->get('tags')->getData()));
            $post->getTags()->clear();
            foreach ($names as $name) {
                $tag = $this->entityManager->getRepository(Tag::class)->findOneBy([
                    'name' => $name
                ]);
                if (\is_null($tag)) {
                    $tag = new Tag;
                    $tag->setName($name);

                    $this->entityManager->persist($tag);
                }

                $post->addTag($tag);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
