<?php

namespace App\Form;

use App\Entity\BlogComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BlogCommentForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                    ])
                ]
            ])
            ->add('email', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ]
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlogComment::class,
        ]);
    }
}
