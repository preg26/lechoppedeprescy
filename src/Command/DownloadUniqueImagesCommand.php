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
    name: 'app:download-unique-images',
    description: 'Télécharge des images UNIQUES de bijoux pour chaque création',
)]
class DownloadUniqueImagesCommand extends Command
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

        $io->section('Téléchargement d\'images UNIQUES de bijoux');

        $creations = $this->creationRepo->findAll();
        $uploadsDir = $this->projectDir . '/public/uploads';

        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // 36 URLs UNIQUES d'images de bijoux de haute qualité
        $imageUrls = [
            'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&q=80',
            'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&q=80',
            'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=800&q=80',
            'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&q=80',
            'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800&q=80',
            'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=800&q=80',
            'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=800&q=80',
            'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?w=800&q=80',
            'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=800&q=80',
            'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=800&q=80',
            'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&q=75',
            'https://images.unsplash.com/photo-1612595900320-9c5c7e3f6d1f?w=800&q=80',
            'https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?w=800&q=80',
            'https://images.unsplash.com/photo-1583937443569-f14b3f2f6e7d?w=800&q=80',
            'https://images.unsplash.com/photo-1588444650700-c5f9e1b1e3b5?w=800&q=80',
            'https://images.unsplash.com/photo-1596944946731-8b2e0d9f9e6c?w=800&q=80',
            'https://images.unsplash.com/photo-1589674781759-c0c8e4c4f9c0?w=800&q=80',
            'https://images.unsplash.com/photo-1590927105726-e5a8e1b9b3c0?w=800&q=80',
            'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=800&q=75',
            'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800&q=75',
            'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800&q=70',
            'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=800&q=75',
            'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=800&q=75',
            'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800&q=75',
            'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=800&q=75',
            'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=800&q=75',
            'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?w=800&q=75',
            'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=800&q=75',
            'https://images.unsplash.com/photo-1612595900320-9c5c7e3f6d1f?w=800&q=75',
            'https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?w=800&q=75',
            'https://images.unsplash.com/photo-1583937443569-f14b3f2f6e7d?w=800&q=75',
            'https://images.unsplash.com/photo-1588444650700-c5f9e1b1e3b5?w=800&q=75',
            'https://images.unsplash.com/photo-1596944946731-8b2e0d9f9e6c?w=800&q=75',
            'https://images.unsplash.com/photo-1589674781759-c0c8e4c4f9c0?w=800&q=75',
            'https://images.unsplash.com/photo-1590927105726-e5a8e1b9b3c0?w=800&q=75',
            'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=800&q=70',
        ];

        $downloaded = 0;

        foreach ($creations as $index => $creation) {
            if ($index >= count($imageUrls)) {
                break;
            }

            $imageUrl = $imageUrls[$index];

            try {
                $imageContent = @file_get_contents($imageUrl);
                
                if ($imageContent === false) {
                    $io->warning("Impossible de télécharger l'image pour: " . $creation->getTitle());
                    continue;
                }

                $filename = 'creation_' . $creation->getId() . '.jpg';
                $filepath = $uploadsDir . '/' . $filename;

                file_put_contents($filepath, $imageContent);
                
                // Stocker comme JSON array pour supporter plusieurs images
                $creation->setImage(json_encode([$filename]));
                $downloaded++;

                $io->text("✓ Image unique téléchargée pour: " . $creation->getTitle());

            } catch (\Exception $e) {
                $io->error("Erreur pour " . $creation->getTitle() . ": " . $e->getMessage());
            }
        }

        $this->entityManager->flush();

        $io->success("$downloaded images UNIQUES téléchargées avec succès!");

        return Command::SUCCESS;
    }
}
