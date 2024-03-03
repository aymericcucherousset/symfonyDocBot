<?php

namespace App\Form;

use App\Service\Document\DocManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChatbotType extends AbstractType
{
    public function __construct(
        private string $docPath,
        private TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(
                        ['message' => ucfirst($this->translator->trans('please.fill.in.this.field'))]
                    ),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'minMessage' => 'Your question should be at least {{ limit }} characters long',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                            'maxMessage' => 'Your question is too long, it should have {{ limit }} characters or less',
                        ]
                    ),
                ],
                'required' => true,
            ])
            ->add('version', ChoiceType::class, [
                'choices' => $this->getAvailableVersions(),
                'required' => true,
            ])
            ->add('submit', SubmitType::class)
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
            $docPath = $this->docPath.$repositoryPath.'/'.$version;
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
