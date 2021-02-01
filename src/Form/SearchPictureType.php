<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchPictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //champ de recherche par mot-clé
            ->add('keyword', SearchType::class, [
                'label' => 'Search',
                'required' => false,
            ])
            ->add('minLikes', NumberType::class, [
                'label' => 'Minimum likes',
                'required' => false,
            ])
            ->add('minDownloads', NumberType::class, [
                'label' => 'Minimum downloads',
                'required' => false,
            ])
            ->add('sort', ChoiceType::class, [
                'label' => 'Sort by',
                'required' => false,
                'placeholder' => 'Choose sort order...',
                'choices' => [
                    'Most downloads first' => 'downloads',
                    'Most likes first' => 'likes',
                    'Most recent first' => 'createdAt',
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'GO'])

            //les form de recherche sont en GET !
            ->setMethod("GET")
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
