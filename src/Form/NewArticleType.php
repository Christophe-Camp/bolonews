<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class NewArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('chapeau')
            ->add('texte')
            ->add('photo',FileType::class,[
                'label' => 'Photo de l article',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
            new File([
                'maxSize' => '2M',
                'mimeTypes' => ['image/*'],
                'mimeTypesMessage' => 'Merci de téléverser une image valide (JPEG, PNG ou WebP).',
                    ]),
                ],
            ])
            /*->add('date_crea', null, [
                'widget' => 'single_text'
            ])
            ->add('date_modif', null, [
                'widget' => 'single_text'
            ])*/
            ->add('publier')
            /*->add('auteur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'pseudo',
            ])*/
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'libelle',
            ])
           /* ->add('likes', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
