<?php
// src/Form/RegistrationType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Email(['message' => 'L\'email n\'est pas valide.']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit comporter au moins {{ limit }} caractères.']),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirmation mot de passe',
                'constraints' => [
                    new NotBlank(['message' => 'La confirmation du mot de passe est obligatoire.']),
                    new EqualTo(['value' => 'password', 'message' => 'Les mots de passe ne correspondent pas.']),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J’accepte les CGU de GreenGoodies',
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez accepter les CGU pour vous inscrire.']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
