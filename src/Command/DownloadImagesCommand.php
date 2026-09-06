<?php

namespace App\Command;

use App\Repository\CreationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:download-images',
    description: 'Télécharge des images de bijoux depuis Unsplash pour les créations',
)]
class DownloadImagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CreationRepository $creationRepo,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Téléchargement des images de bijoux');

        $creations = $this->creationRepo->findAll();
        $uploadsDir = $this->projectDir . '/public/uploads';

        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // URLs d'images de bijoux de haute qualité
        $imageUrls = [
            'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&q=80', // Bague diamant
            'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&q=80', // Bagues délicates
            'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=800&q=80', // Bague artisanale
            'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&q=80', // Bracelets argent
            'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800&q=80', // Bagues or noir
            'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=800&q=80', // Alliances
            'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=800&q=80', // Bague émeraude
            'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?w=800&q=80', // Bague solitaire
            'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&q=80', // Bagues fines
            'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=800&q=80', // Bijoux atelier
        ];

        $imageIndex = 0;
        $downloaded = 0;

        foreach ($creations as $creation) {
            // Utiliser une image différente pour chaque création
            $imageUrl = $imageUrls[$imageIndex % count($imageUrls)];
            $imageIndex++;

            try {
                $imageContent = @file_get_contents($imageUrl);
                
                if ($imageContent === false) {
                    $io->warning("Impossible de télécharger l'image pour: " . $creation->getTitle());
                    continue;
                }

                $filename = 'creation_' . $creation->getId() . '_' . uniqid() . '.jpg';
                $filepath = $uploadsDir . '/' . $filename;

                file_put_contents($filepath, $imageContent);
                
                $creation->setImage($filename);
                $downloaded++;

                $io->text("✓ Image téléchargée pour: " . $creation->getTitle());

            } catch (\Exception $e) {
                $io->error("Erreur pour " . $creation->getTitle() . ": " . $e->getMessage());
            }
        }

        $this->entityManager->flush();

        $io->success("$downloaded images téléchargées avec succès!");

        return Command::SUCCESS;
    }
}
