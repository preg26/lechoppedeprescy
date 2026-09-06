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
    name: 'app:convert-images-grayscale',
    description: 'Convertit physiquement les 2èmes images en noir et blanc',
)]
class ConvertSecondImagesToGrayscaleCommand extends Command
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

        $io->section('Conversion des 2èmes images en noir et blanc');

        $creations = $this->creationRepo->findAll();
        $uploadsDir = $this->projectDir . '/public/uploads';
        $converted = 0;

        foreach ($creations as $creation) {
            $images = $creation->getImages();
            
            if (!$images || count($images) < 2) {
                continue;
            }

            // Prendre la 2ème image
            $secondImage = $images[1];
            $imagePath = $uploadsDir . '/' . $secondImage;

            if (!file_exists($imagePath)) {
                $io->warning("Image non trouvée: $secondImage");
                continue;
            }

            try {
                // Charger l'image
                $imageInfo = getimagesize($imagePath);
                if (!$imageInfo) {
                    $io->warning("Impossible de lire l'image: $secondImage");
                    continue;
                }

                $imageType = $imageInfo[2];
                
                // Créer la ressource image selon le type
                switch ($imageType) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($imagePath);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($imagePath);
                        break;
                    case IMAGETYPE_GIF:
                        $image = imagecreatefromgif($imagePath);
                        break;
                    default:
                        $io->warning("Type d'image non supporté: $secondImage");
                        continue 2;
                }

                if (!$image) {
                    $io->warning("Erreur lors du chargement de l'image: $secondImage");
                    continue;
                }

                // Convertir en noir et blanc
                imagefilter($image, IMG_FILTER_GRAYSCALE);

                // Créer un nouveau nom de fichier
                $pathInfo = pathinfo($secondImage);
                $newFilename = $pathInfo['filename'] . '_bw.' . $pathInfo['extension'];
                $newPath = $uploadsDir . '/' . $newFilename;

                // Sauvegarder l'image en noir et blanc
                switch ($imageType) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($image, $newPath, 85);
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($image, $newPath, 8);
                        break;
                    case IMAGETYPE_GIF:
                        imagegif($image, $newPath);
                        break;
                }

                // Libérer la mémoire
                imagedestroy($image);

                // Mettre à jour la base de données
                $images[1] = $newFilename;
                $creation->setImages($images);
                
                $converted++;
                $io->text("✓ Converti: {$creation->getTitle()} → $newFilename");

            } catch (\Exception $e) {
                $io->error("Erreur pour {$creation->getTitle()}: " . $e->getMessage());
            }
        }

        $this->entityManager->flush();

        $io->success("$converted images converties en noir et blanc!");

        return Command::SUCCESS;
    }
}
