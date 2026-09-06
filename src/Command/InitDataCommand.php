<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Creation;
use App\Entity\PageContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-data',
    description: 'Initialise la base de données avec les contenus, catégories et créations',
)]
class InitDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'production',
            'p',
            InputOption::VALUE_NONE,
            'Mode production : initialise uniquement les contenus et catégories (sans créations)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isProduction = $input->getOption('production');

        if ($isProduction) {
            $io->note('Mode PRODUCTION : initialisation sans créations ni demandes de contact');
        }

        // 1. Créer les contenus de page
        $io->section('Création des contenus de page');
        $this->createPageContents();
        $io->success('Contenus de page créés');

        // 2. Créer les catégories
        $io->section('Création des catégories');
        $categories = $this->createCategories();
        $io->success('Catégories créées');

        // 3. Créer les créations (sauf en mode production)
        if (!$isProduction) {
            $io->section('Création des créations (12 par catégorie)');
            $this->createCreations($categories);
            $io->success('Créations créées');
            $io->success('Base de données initialisée avec succès !');
            $io->note('Total : ' . count($categories) . ' catégories et ' . (count($categories) * 12) . ' créations');
        } else {
            $io->success('Base de données initialisée avec succès !');
            $io->note('Total : ' . count($categories) . ' catégories (créations vides pour production)');
        }

        return Command::SUCCESS;
    }

    private function createPageContents(): void
    {
        $contents = [
            // Hero Section
            ['key' => 'hero_title', 'value' => 'Chaque geste compte', 'section' => 'hero'],
            ['key' => 'hero_subtitle', 'value' => "Atelier de bijouterie où les mains façonnent l'argent et l'or.\nCréation, réparation, transformation — tout commence ici.", 'section' => 'hero'],
            ['key' => 'hero_cta', 'value' => 'Parlons de vos bijoux', 'section' => 'hero'],

            // Savoir-faire Section
            ['key' => 'savoir_faire_title', 'value' => 'Le geste avant tout', 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_subtitle', 'value' => 'Pas de machine, pas de série. Juste des mains, des outils, et du temps pour vous.', 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_box1_title', 'value' => "Sur l'établi", 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_box1_text', 'value' => "Chaque bijou naît ici. Dessiné, façonné, poli. Rien n'est produit en série.", 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_box2_title', 'value' => 'Réparer, pas jeter', 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_box2_text', 'value' => 'Chaîne cassée ? Fermoir fragile ? On répare. Parce que vos bijoux méritent une seconde vie.', 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_box3_title', 'value' => 'Transformer', 'section' => 'savoir_faire'],
            ['key' => 'savoir_faire_box3_text', 'value' => 'Un bijou de famille qui ne vous parle plus ? On le réinvente ensemble. Même matière, nouvelle forme, votre histoire.', 'section' => 'savoir_faire'],

            // Créations Section
            ['key' => 'creations_title', 'value' => "Ce qui sort de l'atelier", 'section' => 'creations'],
            ['key' => 'creations_subtitle', 'value' => 'Quelques exemples. Chaque pièce est unique.', 'section' => 'creations'],

            // Atelier Section
            ['key' => 'atelier_title', 'value' => 'Comment ça marche', 'section' => 'atelier'],
            ['key' => 'atelier_subtitle', 'value' => 'Simple. Direct. Transparent.', 'section' => 'atelier'],
            ['key' => 'atelier_step1_title', 'value' => "Vous m'envoyez une photo", 'section' => 'atelier'],
            ['key' => 'atelier_step1_text', 'value' => 'Via Messenger, le formulaire du site, ou mail. Avec quelques mots sur votre projet.', 'section' => 'atelier'],
            ['key' => 'atelier_step2_title', 'value' => 'On échange ensemble', 'section' => 'atelier'],
            ['key' => 'atelier_step2_text', 'value' => 'Je vous explique ce qui est faisable, le tarif, et les délais. Simple et direct.', 'section' => 'atelier'],
            ['key' => 'atelier_step3_title', 'value' => 'Je crée, vous récupérez', 'section' => 'atelier'],
            ['key' => 'atelier_step3_text', 'value' => "Je travaille à l'atelier avec mes mains. Puis on se voit pour la livraison, ou envoi par la poste.", 'section' => 'atelier'],

            // Contact Section
            ['key' => 'contact_title', 'value' => 'On parle de vos bijoux ?', 'section' => 'contact'],
            ['key' => 'contact_subtitle', 'value' => 'Deux options pour me contacter. Je réponds rapidement.', 'section' => 'contact'],
            ['key' => 'contact_facebook_title', 'value' => 'Via Facebook', 'section' => 'contact'],
            ['key' => 'contact_facebook_text', 'value' => 'Le plus rapide pour échanger et voir mes créations', 'section' => 'contact'],
            ['key' => 'contact_form_title', 'value' => 'Via le site', 'section' => 'contact'],
            ['key' => 'contact_form_text', 'value' => 'Envoyez-moi une demande détaillée', 'section' => 'contact'],
            ['key' => 'contact_response_time', 'value' => 'Réponse sous 24h max. Souvent bien avant.', 'section' => 'contact'],

            // Footer Section
            ['key' => 'footer_brand_name', 'value' => "L'échoppe de Prescy", 'section' => 'footer'],
            ['key' => 'footer_brand_subtitle', 'value' => 'Bijouterie artisanale', 'section' => 'footer'],
            ['key' => 'footer_tagline', 'value' => 'Chaque bijou raconte une histoire', 'section' => 'footer'],
            ['key' => 'footer_service_1', 'value' => 'Création sur mesure', 'section' => 'footer'],
            ['key' => 'footer_service_2', 'value' => 'Réparation', 'section' => 'footer'],
            ['key' => 'footer_service_3', 'value' => 'Transformation', 'section' => 'footer'],
            ['key' => 'footer_service_4', 'value' => 'Gravure', 'section' => 'footer'],
            ['key' => 'footer_contact_method', 'value' => 'Facebook Messenger', 'section' => 'footer'],
            ['key' => 'footer_response_time', 'value' => 'Réponse sous 24h', 'section' => 'footer'],
            ['key' => 'footer_location', 'value' => '📍 Prescy, France', 'section' => 'footer'],
            ['key' => 'footer_copyright', 'value' => "L'échoppe de Prescy. Bijouterie artisanale.", 'section' => 'footer'],
        ];

        foreach ($contents as $data) {
            $content = new PageContent();
            $content->setKey($data['key']);
            $content->setValue($data['value']);
            $content->setSection($data['section']);
            $this->entityManager->persist($content);
        }

        $this->entityManager->flush();
    }

    private function createCategories(): array
    {
        $categoriesData = [
            ['name' => 'Créations', 'slug' => 'creation', 'description' => 'Bijoux sur mesure créés spécialement pour vous'],
            ['name' => 'Réparations', 'slug' => 'reparation', 'description' => 'Réparation et restauration de vos bijoux précieux'],
            ['name' => 'Transformations', 'slug' => 'transformation', 'description' => 'Transformation de bijoux anciens en nouvelles créations'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $category->setDescription($data['description']);
            $this->entityManager->persist($category);
            $categories[] = $category;
        }

        $this->entityManager->flush();
        return $categories;
    }

    private function createCreations(array $categories): void
    {
        $creationsData = [
            // Créations (12)
            [
                'category' => 0,
                'items' => [
                    ['title' => 'Bague solitaire en or blanc', 'description' => 'Bague élégante en or blanc 18 carats avec diamant central. Création unique façonnée à la main.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Alliance gravée personnalisée', 'description' => 'Alliance en or jaune avec gravure intérieure personnalisée. Votre date, vos mots, gravés à la main.', 'image' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=600&q=75'],
                    ['title' => 'Bague chevalière moderne', 'description' => 'Chevalière contemporaine en argent massif. Design épuré et intemporel.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Bague trilogie diamants', 'description' => 'Trois diamants sertis sur or blanc. Symbolise passé, présent et futur.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Bague vintage art déco', 'description' => "Inspiration art déco avec pierres fines. Création originale dans l'esprit des années 20.", 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600&q=75'],
                    ['title' => 'Bague jonc émeraude', 'description' => 'Jonc en or jaune serti d\'une émeraude naturelle. Élégance et sobriété.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Bague entrelacée deux ors', 'description' => 'Design entrelacé en or blanc et or jaune. Symbolise l\'union et la complémentarité.', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600&q=75'],
                    ['title' => 'Bague marquise saphir', 'description' => 'Saphir bleu taille marquise sur monture or blanc. Pièce raffinée et élégante.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Bague nature feuillage', 'description' => 'Inspiration nature avec motif feuillage en or. Chaque détail ciselé à la main.', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600&q=75'],
                    ['title' => 'Bague pavage diamants', 'description' => 'Pavage complet de diamants sur or blanc. Brillance et éclat garantis.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Bague perle baroque', 'description' => 'Perle baroque montée sur argent. Pièce unique et naturelle.', 'image' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=600&q=75'],
                    ['title' => 'Bague géométrique moderne', 'description' => 'Design géométrique contemporain en argent. Lignes pures et audacieuses.', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600&q=75'],
                ]
            ],
            // Réparations (12)
            [
                'category' => 1,
                'items' => [
                    ['title' => 'Réparation chaîne cassée', 'description' => 'Soudure invisible de chaîne en or. Solidité retrouvée comme au premier jour.', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600&q=75'],
                    ['title' => 'Remplacement fermoir', 'description' => 'Changement de fermoir usé par un fermoir de sécurité. Votre bijou ne vous quittera plus.', 'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600&q=75'],
                    ['title' => 'Resserrage pierre', 'description' => 'Resserrage de griffes et sertissage de pierre. Sécurité maximale pour vos pierres précieuses.', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600&q=75'],
                    ['title' => 'Mise à taille bague', 'description' => 'Ajustement de taille de bague. Précision au dixième de millimètre.', 'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600&q=75'],
                    ['title' => 'Polissage et ravivage', 'description' => 'Polissage professionnel pour redonner tout son éclat à votre bijou.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Réparation anneau cassé', 'description' => 'Soudure et renfort d\'anneau de bague. Réparation durable et esthétique.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Changement de maillon', 'description' => 'Remplacement de maillons endommagés sur bracelet. Harmonie parfaite avec l\'existant.', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600&q=75'],
                    ['title' => 'Réparation boucle d\'oreille', 'description' => 'Réparation de système de fermeture. Confort et sécurité retrouvés.', 'image' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=600&q=75'],
                    ['title' => 'Redressage bijou déformé', 'description' => 'Redressage et remise en forme de bijou. Retrouvez la forme originale.', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600&q=75'],
                    ['title' => 'Remplacement pierre perdue', 'description' => 'Recherche et remplacement de pierre identique. Votre bijou retrouve son intégrité.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Réparation monture cassée', 'description' => 'Reconstruction de monture endommagée. Savoir-faire et précision.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Nettoyage profond', 'description' => 'Nettoyage en profondeur et entretien complet. Votre bijou comme neuf.', 'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600&q=75'],
                ]
            ],
            // Transformations (12)
            [
                'category' => 2,
                'items' => [
                    ['title' => 'Bague vintage modernisée', 'description' => 'Transformation de bague ancienne en design contemporain. Même or, nouvelle vie.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Pendentif devenu bague', 'description' => 'Transformation de pendentif en bague élégante. Récupération des pierres et de l\'or.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Alliance réinventée', 'description' => 'Transformation d\'alliance en bague moderne. Votre histoire continue autrement.', 'image' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=600&q=75'],
                    ['title' => 'Broche transformée', 'description' => 'Ancienne broche devenue pendentif contemporain. Héritage préservé, style renouvelé.', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600&q=75'],
                    ['title' => 'Boucles d\'oreilles réimaginées', 'description' => 'Transformation de boucles anciennes en design actuel. Pierres conservées, monture renouvelée.', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600&q=75'],
                    ['title' => 'Collier familial modernisé', 'description' => 'Bijou de famille transformé en pièce contemporaine. Respect de l\'histoire, vision moderne.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Chevalière réinterprétée', 'description' => 'Ancienne chevalière transformée en bague épurée. Gravure préservée, design actualisé.', 'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=600&q=75'],
                    ['title' => 'Bracelet recomposé', 'description' => 'Transformation de plusieurs bijoux en bracelet unique. Fusion créative de vos souvenirs.', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=600&q=75'],
                    ['title' => 'Bague art déco revisitée', 'description' => 'Transformation respectueuse d\'une bague art déco. Essence préservée, confort moderne.', 'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=600&q=75'],
                    ['title' => 'Solitaire transformé', 'description' => 'Ancien solitaire devenu bague entourage. Même diamant, nouvel écrin.', 'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343a?w=600&q=75'],
                    ['title' => 'Jonc réinventé', 'description' => 'Transformation de jonc massif en bague ajourée. Légèreté et modernité.', 'image' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?w=600&q=75'],
                    ['title' => 'Parure dissociée', 'description' => 'Ancienne parure transformée en pièces indépendantes. Polyvalence et style contemporain.', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600&q=75'],
                ]
            ],
        ];

        foreach ($creationsData as $categoryData) {
            $category = $categories[$categoryData['category']];
            
            foreach ($categoryData['items'] as $itemData) {
                $creation = new Creation();
                $creation->setTitle($itemData['title']);
                $creation->setDescription($itemData['description']);
                $creation->setCategory($category);
                $creation->setIsPublished(true);
                // Note: Les images sont des URLs Unsplash, pas des fichiers uploadés
                // Dans un vrai contexte, il faudrait télécharger et stocker ces images
                $this->entityManager->persist($creation);
            }
        }

        $this->entityManager->flush();
    }
}
