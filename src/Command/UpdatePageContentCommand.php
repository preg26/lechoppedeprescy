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
    name: 'app:update-page-content',
    description: 'Met à jour les contenus de page avec le contenu actuel du site',
)]
class UpdatePageContentCommand extends Command
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

        $io->section('Mise à jour des contenus de page');

        $contents = [
            // Hero Section
            ['section' => 'hero', 'key' => 'title', 'value' => 'Chaque geste compte'],
            ['section' => 'hero', 'key' => 'subtitle', 'value' => 'Atelier de bijouterie où les mains façonnent l\'argent et l\'or.<br>Création, réparation, transformation — tout commence ici.'],
            ['section' => 'hero', 'key' => 'cta_text', 'value' => 'Parlons de vos bijoux'],
            
            // L'Artisan Section
            ['section' => 'artisan', 'key' => 'title', 'value' => 'L\'Artisan'],
            ['section' => 'artisan', 'key' => 'subtitle', 'value' => 'Pas de machine, pas de série. Juste des mains, des outils, et du temps pour vous.'],
            ['section' => 'artisan', 'key' => 'feature1_title', 'value' => 'Sur l\'établi'],
            ['section' => 'artisan', 'key' => 'feature1_text', 'value' => 'Limer, souder, polir. Chaque pièce passe par mes mains. Aucun raccourci, juste la précision du geste répété.'],
            ['section' => 'artisan', 'key' => 'feature2_title', 'value' => 'Réparer, pas jeter'],
            ['section' => 'artisan', 'key' => 'feature2_text', 'value' => 'Chaîne cassée ? Fermoir fragile ? On répare. Parce que vos bijoux méritent une seconde vie.'],
            ['section' => 'artisan', 'key' => 'feature3_title', 'value' => 'Transformer'],
            ['section' => 'artisan', 'key' => 'feature3_text', 'value' => 'Un bijou de famille qui ne vous parle plus ? On le réinvente ensemble. Même matière, nouvelle forme, votre histoire.'],
            
            // Galerie Section
            ['section' => 'galerie', 'key' => 'title', 'value' => 'Galerie'],
            ['section' => 'galerie', 'key' => 'subtitle', 'value' => 'Quelques exemples. Chaque pièce est unique.'],
            
            // Processus Section
            ['section' => 'processus', 'key' => 'title', 'value' => 'Processus de Création'],
            ['section' => 'processus', 'key' => 'subtitle', 'value' => 'Simple. Direct. Transparent.'],
            ['section' => 'processus', 'key' => 'step1_title', 'value' => 'Vous m\'envoyez une photo'],
            ['section' => 'processus', 'key' => 'step1_text', 'value' => 'Via Messenger, le formulaire du site, ou mail. Avec quelques mots sur votre projet.'],
            ['section' => 'processus', 'key' => 'step2_title', 'value' => 'On échange ensemble'],
            ['section' => 'processus', 'key' => 'step2_text', 'value' => 'Je vous explique ce qui est faisable, le tarif, et les délais. Simple et direct.'],
            ['section' => 'processus', 'key' => 'step3_title', 'value' => 'Je crée, vous récupérez'],
            ['section' => 'processus', 'key' => 'step3_text', 'value' => 'Je travaille à l\'atelier avec mes mains. Puis on se voit pour la livraison, ou envoi par la poste.'],
            
            // Contact Section
            ['section' => 'contact', 'key' => 'title', 'value' => 'On parle de vos bijoux ?'],
            ['section' => 'contact', 'key' => 'subtitle', 'value' => 'Deux options pour me contacter. Je réponds rapidement.'],
            ['section' => 'contact', 'key' => 'facebook_title', 'value' => 'Via Facebook'],
            ['section' => 'contact', 'key' => 'facebook_text', 'value' => 'Le plus rapide pour échanger et voir mes créations'],
            ['section' => 'contact', 'key' => 'facebook_button', 'value' => 'Messenger'],
            ['section' => 'contact', 'key' => 'form_title', 'value' => 'Via le site'],
            ['section' => 'contact', 'key' => 'form_text', 'value' => 'Envoyez-moi une demande détaillée'],
            ['section' => 'contact', 'key' => 'form_button', 'value' => 'Formulaire'],
            ['section' => 'contact', 'key' => 'response_time', 'value' => 'Réponse sous 24h max. Souvent bien avant.'],
        ];

        $updated = 0;
        $created = 0;

        foreach ($contents as $contentData) {
            $content = $this->contentRepo->findOneBy([
                'section' => $contentData['section'],
                'key' => $contentData['key']
            ]);

            if ($content) {
                $content->setValue($contentData['value']);
                $updated++;
                $io->text("✓ Mis à jour: {$contentData['section']}.{$contentData['key']}");
            } else {
                $content = new PageContent();
                $content->setSection($contentData['section']);
                $content->setKey($contentData['key']);
                $content->setValue($contentData['value']);
                $this->entityManager->persist($content);
                $created++;
                $io->text("+ Créé: {$contentData['section']}.{$contentData['key']}");
            }
        }

        $this->entityManager->flush();

        $io->success("$created contenus créés, $updated contenus mis à jour!");

        return Command::SUCCESS;
    }
}
