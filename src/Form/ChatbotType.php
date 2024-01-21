<?php

namespace App\Form;

use App\Service\DocManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatbotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Question',
                'attr' => [
                    'placeholder' => 'Posez votre question',
                ],
                'required' => true,
            ])
            ->add('version', ChoiceType::class, [
                'label' => 'Version',
                'choices' => $this->getAvailableVersions(),
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Poser la question',
            ])
        ;
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableVersions(
        string $user = DocManager::SYMFONY_USER,
        string $repository = DocManager::SYMFONY_REPOSITORY
    ): array {
        $repositoryPath = $user.'-'.$repository;
        $versions = [];

        foreach (DocManager::SYMFONY_VERSIONS as $version) {
            $docPath = $repositoryPath.'/'.$version;
            // Check if the version is available
            if (file_exists($docPath)) {
                $versions[$version] = $version;
            }
        }

        return $versions;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
