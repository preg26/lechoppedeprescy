<?php

namespace App\Command;

use App\Entity\PageContent;
use App\Repository\PageContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-footer-content',
    description: 'Ajoute les contenus du footer dans la base de données',
)]
class AddFooterContentCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PageContentRepository $contentRepo
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $footerContents = [
            ['key' => 'footer_brand_name', 'value' => "L'échoppe de Prescy"],
            ['key' => 'footer_brand_subtitle', 'value' => 'Bijouterie artisanale'],
            ['key' => 'footer_tagline', 'value' => 'Chaque bijou raconte une histoire'],
            ['key' => 'footer_service_1', 'value' => 'Création sur mesure'],
            ['key' => 'footer_service_2', 'value' => 'Réparation'],
            ['key' => 'footer_service_3', 'value' => 'Transformation'],
            ['key' => 'footer_service_4', 'value' => 'Gravure'],
            ['key' => 'footer_contact_method', 'value' => 'Facebook Messenger'],
            ['key' => 'footer_response_time', 'value' => 'Réponse sous 24h'],
            ['key' => 'footer_location', 'value' => '📍 Prescy, France'],
            ['key' => 'footer_copyright', 'value' => "L'échoppe de Prescy. Bijouterie artisanale."],
        ];

        $added = 0;
        $skipped = 0;

        foreach ($footerContents as $data) {
            // Vérifier si le contenu existe déjà
            $existing = $this->contentRepo->findOneBy(['key' => $data['key']]);
            
            if ($existing) {
                $skipped++;
                continue;
            }

            $content = new PageContent();
            $content->setKey($data['key']);
            $content->setValue($data['value']);
            $content->setSection('footer');
            $this->entityManager->persist($content);
            $added++;
        }

        $this->entityManager->flush();

        $io->success("Contenus du footer ajoutés : $added");
        if ($skipped > 0) {
            $io->note("Contenus déjà existants ignorés : $skipped");
        }

        return Command::SUCCESS;
    }
}
