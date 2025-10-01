<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CreativeProfile;
use App\Models\OpportunityOwnerProfile;
use App\Models\Job;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable Scout indexing during seeding to avoid Meilisearch connection issues
        $scoutEnabled = config('scout.driver');
        config(['scout.driver' => null]);

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'user_type' => 'creative',
                'is_admin' => true,
                'profile_completed_at' => now(),
                'profile_completion_score' => 100,
            ]
        );

        // Creative professionals data
        $creatives = [
            // Film & Animation
            ['name' => 'Budi Santoso', 'email' => 'budi.filmmaker@example.com', 'skills' => ['Sinematografi', 'Editing Video', 'Adobe Premiere', 'After Effects'], 'bio' => 'Sutradara dan editor video berpengalaman 8 tahun dalam industri film dokumenter dan komersial.', 'category' => 'Film & Animasi', 'hourly_rate' => 150000, 'location' => 'Jakarta'],
            ['name' => 'Sari Dewi', 'email' => 'sari.animator@example.com', 'skills' => ['Animasi 2D', 'Character Design', 'Toon Boom', 'Adobe Animate'], 'bio' => 'Animator 2D profesional dengan spesialisasi karakter animasi untuk film pendek dan series.', 'category' => 'Film & Animasi', 'hourly_rate' => 120000, 'location' => 'Bandung'],
            ['name' => 'Rahman Hakim', 'email' => 'rahman.motion@example.com', 'skills' => ['Motion Graphics', 'Cinema 4D', 'Adobe After Effects', 'Blender'], 'bio' => 'Motion graphics designer dengan fokus pada iklan dan konten digital interaktif.', 'category' => 'Film & Animasi', 'hourly_rate' => 135000, 'location' => 'Yogyakarta'],

            // Music
            ['name' => 'Andi Prasetyo', 'email' => 'andi.producer@example.com', 'skills' => ['Produksi Musik', 'Mixing', 'Mastering', 'Ableton Live'], 'bio' => 'Produser musik elektronik dan pop dengan pengalaman di industri musik Indonesia selama 6 tahun.', 'category' => 'Musik', 'hourly_rate' => 200000, 'location' => 'Jakarta'],
            ['name' => 'Fitri Maharani', 'email' => 'fitri.composer@example.com', 'skills' => ['Komposisi', 'Piano', 'Orchestration', 'Logic Pro'], 'bio' => 'Komposer musik film dan jingle dengan background klasik dan kontemporer.', 'category' => 'Musik', 'hourly_rate' => 175000, 'location' => 'Surabaya'],
            ['name' => 'Dito Anggoro', 'email' => 'dito.sound@example.com', 'skills' => ['Sound Design', 'Audio Engineering', 'Pro Tools', 'Field Recording'], 'bio' => 'Sound engineer dan sound designer untuk podcast, film, dan media digital.', 'category' => 'Musik', 'hourly_rate' => 125000, 'location' => 'Bandung'],

            // Fashion
            ['name' => 'Maya Sariputri', 'email' => 'maya.fashion@example.com', 'skills' => ['Fashion Design', 'Pattern Making', 'Adobe Illustrator', 'Sustainable Fashion'], 'bio' => 'Fashion designer dengan fokus pada sustainable fashion dan ready-to-wear collection.', 'category' => 'Fashion', 'hourly_rate' => 100000, 'location' => 'Jakarta'],
            ['name' => 'Indra Kusuma', 'email' => 'indra.stylist@example.com', 'skills' => ['Fashion Styling', 'Photography Direction', 'Trend Analysis', 'Wardrobe Consulting'], 'bio' => 'Fashion stylist dan konsultan untuk brand fashion, artis, dan editorial magazine.', 'category' => 'Fashion', 'hourly_rate' => 85000, 'location' => 'Jakarta'],
            ['name' => 'Rini Handayani', 'email' => 'rini.textile@example.com', 'skills' => ['Textile Design', 'Batik', 'Natural Dyeing', 'Weaving'], 'bio' => 'Desainer tekstil tradisional dengan inovasi modern untuk fashion dan interior.', 'category' => 'Fashion', 'hourly_rate' => 90000, 'location' => 'Solo'],

            // Crafts (Kriya)
            ['name' => 'Agus Wijaya', 'email' => 'agus.woodcraft@example.com', 'skills' => ['Wood Carving', 'Furniture Design', 'Product Design', 'CNC Operation'], 'bio' => 'Pengrajin kayu dan desainer furniture dengan teknik tradisional dan modern.', 'category' => 'Kriya', 'hourly_rate' => 75000, 'location' => 'Jepara'],
            ['name' => 'Lestari Putri', 'email' => 'lestari.ceramic@example.com', 'skills' => ['Keramik', 'Pottery', 'Glazing', 'Sculpture'], 'bio' => 'Seniman keramik dengan spesialisasi pada keramik fungsional dan seni instalasi.', 'category' => 'Kriya', 'hourly_rate' => 65000, 'location' => 'Yogyakarta'],
            ['name' => 'Hendra Saputra', 'email' => 'hendra.metalwork@example.com', 'skills' => ['Metal Craft', 'Jewelry Design', 'Silver Working', 'Bronze Casting'], 'bio' => 'Pengrajin logam dan desainer perhiasan dengan teknik tradisional Bali dan Jawa.', 'category' => 'Kriya', 'hourly_rate' => 80000, 'location' => 'Denpasar'],

            // Culinary
            ['name' => 'Chef Kartika', 'email' => 'kartika.chef@example.com', 'skills' => ['Indonesian Cuisine', 'Menu Development', 'Food Photography', 'Restaurant Management'], 'bio' => 'Chef dengan spesialisasi masakan Nusantara modern dan konsep kuliner inovatif.', 'category' => 'Kuliner', 'hourly_rate' => 120000, 'location' => 'Jakarta'],
            ['name' => 'Bayu Firmansyah', 'email' => 'bayu.baker@example.com', 'skills' => ['Baking', 'Pastry', 'Cake Design', 'Food Styling'], 'bio' => 'Pastry chef dan food stylist untuk fotografi kuliner dan event catering.', 'category' => 'Kuliner', 'hourly_rate' => 95000, 'location' => 'Surabaya'],
            ['name' => 'Dewi Kusumawati', 'email' => 'dewi.beverage@example.com', 'skills' => ['Barista', 'Coffee Roasting', 'Latte Art', 'Beverage Innovation'], 'bio' => 'Barista profesional dan coffee educator dengan sertifikasi internasional.', 'category' => 'Kuliner', 'hourly_rate' => 75000, 'location' => 'Bandung'],

            // Apps & Games
            ['name' => 'Rizki Pratama', 'email' => 'rizki.mobile@example.com', 'skills' => ['Flutter', 'React Native', 'UI/UX Design', 'Firebase'], 'bio' => 'Mobile app developer dengan fokus pada aplikasi e-commerce dan fintech.', 'category' => 'Aplikasi & Game', 'hourly_rate' => 180000, 'location' => 'Jakarta'],
            ['name' => 'Sinta Cahyani', 'email' => 'sinta.game@example.com', 'skills' => ['Unity', 'C#', 'Game Design', '2D Art'], 'bio' => 'Game developer dan designer untuk mobile games dan educational games.', 'category' => 'Aplikasi & Game', 'hourly_rate' => 165000, 'location' => 'Bandung'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.web@example.com', 'skills' => ['Laravel', 'Vue.js', 'MySQL', 'AWS'], 'bio' => 'Full-stack web developer dengan spesialisasi sistem informasi dan platform digital.', 'category' => 'Aplikasi & Game', 'hourly_rate' => 175000, 'location' => 'Yogyakarta'],

            // Additional creatives for variety
            ['name' => 'Nadia Puspita', 'email' => 'nadia.graphic@example.com', 'skills' => ['Graphic Design', 'Branding', 'Adobe Creative Suite', 'Print Design'], 'bio' => 'Graphic designer dengan pengalaman branding untuk startup dan UMKM Indonesia.', 'category' => 'Desain Grafis', 'hourly_rate' => 85000, 'location' => 'Jakarta'],
            ['name' => 'Tommy Wijaksono', 'email' => 'tommy.photo@example.com', 'skills' => ['Photography', 'Product Photography', 'Portrait', 'Lightroom'], 'bio' => 'Fotografer komersial untuk produk fashion, kuliner, dan corporate events.', 'category' => 'Fotografi', 'hourly_rate' => 110000, 'location' => 'Surabaya'],
            ['name' => 'Lia Permatasari', 'email' => 'lia.content@example.com', 'skills' => ['Content Writing', 'SEO', 'Social Media', 'Copywriting'], 'bio' => 'Content creator dan copywriter untuk brand digital dan media sosial.', 'category' => 'Konten Digital', 'hourly_rate' => 70000, 'location' => 'Bandung'],
        ];

        // Create creative users and profiles
        foreach ($creatives as $creative) {
            $user = User::firstOrCreate(
                ['email' => $creative['email']],
                [
                    'name' => $creative['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'user_type' => 'creative',
                    'is_admin' => false,
                    'profile_completed_at' => now(),
                    'profile_completion_score' => rand(75, 100),
                ]
            );

            CreativeProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $creative['bio'],
                    'skills' => $creative['skills'],
                    'portfolio_links' => [
                        'https://portfolio-' . Str::slug($creative['name']) . '.com',
                        'https://behance.net/' . Str::slug($creative['name']),
                    ],
                    'location' => $creative['location'],
                    'hourly_rate' => $creative['hourly_rate'],
                    'experience_level' => rand(1, 3) == 1 ? 'beginner' : (rand(1, 2) == 1 ? 'intermediate' : 'expert'),
                    'available_for_work' => rand(1, 4) != 1, // 75% available
                ]
            );
        }

        // Opportunity owners data
        $companies = [
            ['name' => 'PT Kreatif Digital Indonesia', 'email' => 'hr@kreatifdigital.com', 'company' => 'PT Kreatif Digital Indonesia', 'description' => 'Agensi digital marketing yang melayani brand lokal dan multinasional.', 'industry' => 'Digital Marketing', 'size' => '50-100'],
            ['name' => 'Sarah Putri', 'email' => 'sarah@batikmodern.com', 'company' => 'Batik Modern Studio', 'description' => 'Brand fashion sustainable dengan fokus pada modernisasi batik tradisional.', 'industry' => 'Fashion', 'size' => '10-50'],
            ['name' => 'Budi Hartono', 'email' => 'budi@mediaproduksi.com', 'company' => 'Media Produksi Nusantara', 'description' => 'Rumah produksi konten video untuk platform digital dan televisi.', 'industry' => 'Media Production', 'size' => '20-50'],
            ['name' => 'Ani Kusuma', 'email' => 'ani@rasakuliner.com', 'company' => 'Rasa Kuliner Network', 'description' => 'Jaringan restoran dan cloud kitchen dengan konsep fusion food Indonesia.', 'industry' => 'Food & Beverage', 'size' => '100-500'],
            ['name' => 'Rahmat Wijaya', 'email' => 'rahmat@gamelokal.com', 'company' => 'Game Lokal Studio', 'description' => 'Studio pengembangan game mobile dengan tema budaya Indonesia.', 'industry' => 'Gaming', 'size' => '10-50'],
            ['name' => 'Linda Sari', 'email' => 'linda@handmadecraft.com', 'company' => 'Handmade Craft Collective', 'description' => 'Platform marketplace untuk produk kerajinan tangan Indonesia.', 'industry' => 'E-commerce', 'size' => '20-50'],
            ['name' => 'Dicky Prasetyo', 'email' => 'dicky@musiknusantara.com', 'company' => 'Musik Nusantara Label', 'description' => 'Label musik indie yang mempromosikan musisi lokal Indonesia.', 'industry' => 'Music', 'size' => '5-20'],
            ['name' => 'Maya Indira', 'email' => 'maya@eventkreatif.com', 'company' => 'Event Kreatif Indonesia', 'description' => 'Event organizer untuk pameran seni, fashion show, dan festival budaya.', 'industry' => 'Event Management', 'size' => '20-50'],
        ];

        // Create opportunity owner users and profiles
        foreach ($companies as $company) {
            $user = User::firstOrCreate(
                ['email' => $company['email']],
                [
                    'name' => $company['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'user_type' => 'opportunity_owner',
                    'is_admin' => false,
                    'profile_completed_at' => now(),
                    'profile_completion_score' => rand(80, 100),
                ]
            );

            OpportunityOwnerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $company['company'],
                    'company_description' => $company['description'],
                    'company_website' => 'https://' . Str::slug($company['company']) . '.com',
                    'company_size' => $company['size'],
                    'industry' => $company['industry'],
                    'is_verified' => rand(1, 3) != 1, // 66% verified
                    'verified_at' => rand(1, 3) != 1 ? now()->subDays(rand(1, 30)) : null,
                ]
            );
        }

        // Job postings data
        $jobs = [
            // Film & Animation jobs
            ['title' => 'Video Editor untuk Konten YouTube Channel', 'category' => 'Film & Animasi', 'skills' => ['Video Editing', 'Adobe Premiere', 'Color Grading'], 'description' => 'Mencari video editor berpengalaman untuk channel YouTube dengan 500K+ subscribers. Konten lifestyle dan travel.', 'budget_min' => 3000000, 'budget_max' => 5000000, 'location' => 'Jakarta', 'remote' => true],
            ['title' => 'Animator 2D untuk Serial Edukasi Anak', 'category' => 'Film & Animasi', 'skills' => ['Animasi 2D', 'Character Design', 'Adobe Animate'], 'description' => 'Proyek serial animasi edukasi untuk anak-anak dengan karakter lokal Indonesia. Durasi 6 bulan.', 'budget_min' => 15000000, 'budget_max' => 25000000, 'location' => 'Bandung', 'remote' => false],
            ['title' => 'Motion Graphics Designer untuk Campaign Digital', 'category' => 'Film & Animasi', 'skills' => ['Motion Graphics', 'After Effects', 'Cinema 4D'], 'description' => 'Membuat motion graphics untuk campaign produk teknologi. Timeline 2 bulan.', 'budget_min' => 8000000, 'budget_max' => 12000000, 'location' => 'Jakarta', 'remote' => true],

            // Music jobs
            ['title' => 'Produser Musik untuk Album Indie Pop', 'category' => 'Musik', 'skills' => ['Produksi Musik', 'Mixing', 'Mastering'], 'description' => 'Mencari produser musik untuk album debut band indie pop. 8 tracks dengan budget professional.', 'budget_min' => 20000000, 'budget_max' => 35000000, 'location' => 'Jakarta', 'remote' => false],
            ['title' => 'Komposer untuk Soundtrack Film Pendek', 'category' => 'Musik', 'skills' => ['Komposisi', 'Orchestration', 'Logic Pro'], 'description' => 'Film pendek drama dengan durasi 30 menit membutuhkan original soundtrack yang emosional.', 'budget_min' => 5000000, 'budget_max' => 10000000, 'location' => 'Yogyakarta', 'remote' => true],
            ['title' => 'Sound Designer untuk Podcast Series', 'category' => 'Musik', 'skills' => ['Sound Design', 'Audio Engineering', 'Pro Tools'], 'description' => 'Podcast mystery series 10 episode membutuhkan sound design dan audio engineering.', 'budget_min' => 7000000, 'budget_max' => 12000000, 'location' => 'Surabaya', 'remote' => true],

            // Fashion jobs
            ['title' => 'Fashion Designer untuk Koleksi Sustainable', 'category' => 'Fashion', 'skills' => ['Fashion Design', 'Sustainable Fashion', 'Pattern Making'], 'description' => 'Brand fashion sustainable mencari designer untuk koleksi ready-to-wear spring/summer.', 'budget_min' => 12000000, 'budget_max' => 20000000, 'location' => 'Jakarta', 'remote' => false],
            ['title' => 'Fashion Stylist untuk Brand Photoshoot', 'category' => 'Fashion', 'skills' => ['Fashion Styling', 'Photography Direction', 'Trend Analysis'], 'description' => 'Photoshoot campaign untuk brand lokal membutuhkan stylist dengan portfolio fashion editorial.', 'budget_min' => 4000000, 'budget_max' => 7000000, 'location' => 'Jakarta', 'remote' => false],
            ['title' => 'Desainer Tekstil untuk Koleksi Batik Modern', 'category' => 'Fashion', 'skills' => ['Textile Design', 'Batik', 'Pattern Design'], 'description' => 'Kolaborasi dengan pengrajin batik untuk menciptakan motif kontemporer dengan nilai tradisional.', 'budget_min' => 8000000, 'budget_max' => 15000000, 'location' => 'Solo', 'remote' => false],

            // Crafts jobs
            ['title' => 'Pengrajin Furniture Custom untuk Hotel Boutique', 'category' => 'Kriya', 'skills' => ['Wood Carving', 'Furniture Design', 'Custom Furniture'], 'description' => 'Hotel boutique membutuhkan furniture custom dengan sentuhan tradisional Jawa. 50 pieces.', 'budget_min' => 25000000, 'budget_max' => 40000000, 'location' => 'Jepara', 'remote' => false],
            ['title' => 'Seniman Keramik untuk Instalasi Seni', 'category' => 'Kriya', 'skills' => ['Keramik', 'Sculpture', 'Art Installation'], 'description' => 'Gallery seni membutuhkan instalasi keramik untuk pameran group contemporary art.', 'budget_min' => 15000000, 'budget_max' => 25000000, 'location' => 'Yogyakarta', 'remote' => false],
            ['title' => 'Desainer Perhiasan untuk Brand Premium', 'category' => 'Kriya', 'skills' => ['Jewelry Design', 'Silver Working', 'Gold Working'], 'description' => 'Brand perhiasan premium launching koleksi inspired by Indonesian heritage. 20 designs.', 'budget_min' => 18000000, 'budget_max' => 30000000, 'location' => 'Denpasar', 'remote' => false],

            // Culinary jobs
            ['title' => 'Head Chef untuk Restoran Fine Dining Indonesia', 'category' => 'Kuliner', 'skills' => ['Indonesian Cuisine', 'Menu Development', 'Kitchen Management'], 'description' => 'Restoran fine dining dengan konsep modern Indonesian cuisine membutuhkan head chef berpengalaman.', 'budget_min' => 8000000, 'budget_max' => 15000000, 'location' => 'Jakarta', 'remote' => false],
            ['title' => 'Pastry Chef untuk Wedding Cake Business', 'category' => 'Kuliner', 'skills' => ['Pastry', 'Cake Design', 'Sugar Art'], 'description' => 'Wedding cake business expanding membutuhkan pastry chef untuk luxury wedding market.', 'budget_min' => 6000000, 'budget_max' => 10000000, 'location' => 'Surabaya', 'remote' => false],
            ['title' => 'Coffee Consultant untuk Chain Cafe', 'category' => 'Kuliner', 'skills' => ['Coffee Roasting', 'Barista Training', 'Quality Control'], 'description' => 'Chain cafe lokal membutuhkan consultant untuk standardisasi kualitas coffee di 20 outlets.', 'budget_min' => 12000000, 'budget_max' => 20000000, 'location' => 'Bandung', 'remote' => false],

            // Apps & Games jobs
            ['title' => 'Flutter Developer untuk Fintech App', 'category' => 'Aplikasi & Game', 'skills' => ['Flutter', 'Firebase', 'Payment Gateway', 'Security'], 'description' => 'Startup fintech membutuhkan mobile app developer untuk payment solution B2B. 6 months project.', 'budget_min' => 30000000, 'budget_max' => 50000000, 'location' => 'Jakarta', 'remote' => true],
            ['title' => 'Game Developer untuk Educational Mobile Game', 'category' => 'Aplikasi & Game', 'skills' => ['Unity', 'C#', 'Game Design', 'Educational Content'], 'description' => 'NGO pendidikan membutuhkan mobile game edukasi untuk anak SD dengan konten lokal Indonesia.', 'budget_min' => 25000000, 'budget_max' => 40000000, 'location' => 'Bandung', 'remote' => true],
            ['title' => 'Full Stack Developer untuk E-commerce Platform', 'category' => 'Aplikasi & Game', 'skills' => ['Laravel', 'Vue.js', 'MySQL', 'E-commerce'], 'description' => 'Platform e-commerce untuk UMKM membutuhkan developer untuk fitur marketplace dan payment integration.', 'budget_min' => 35000000, 'budget_max' => 55000000, 'location' => 'Yogyakarta', 'remote' => true],
        ];

        // Get all opportunity owner users
        $opportunityOwners = User::where('user_type', 'opportunity_owner')->get();

        // Create job postings
        foreach ($jobs as $index => $job) {
            $owner = $opportunityOwners->random();
            $slug = Str::slug($job['title']) . '-' . ($index + 1);

            Job::firstOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $owner->id,
                    'title' => $job['title'],
                    'location' => $job['location'],
                    'is_remote' => $job['remote'],
                    'status' => rand(1, 10) > 2 ? 'published' : 'draft', // 80% published
                    'compensation_type' => 'project',
                    'tags' => ['Kreatif', 'Indonesia', $job['category']],
                    'category' => $job['category'],
                    'skills' => $job['skills'],
                    'summary' => Str::limit($job['description'], 150),
                    'description' => $job['description'] . "\n\nProyek ini merupakan bagian dari pengembangan industri kreatif Indonesia. Kami mencari profesional yang memiliki passion terhadap budaya lokal dan inovasi kreatif.\n\nRequirements:\n- Portfolio yang relevan\n- Pengalaman minimal 2 tahun\n- Kemampuan komunikasi yang baik\n- Deadline oriented\n\nBenefit:\n- Kompensasi kompetitif\n- Flexible working arrangement\n- Networking dengan industry professionals\n- Credit dalam project final",
                    'published_at' => rand(1, 10) > 2 ? now()->subDays(rand(1, 30)) : null,
                    'timeline_start' => now()->addDays(rand(7, 30)),
                    'timeline_end' => now()->addDays(rand(60, 180)),
                    'budget_min' => $job['budget_min'],
                    'budget_max' => $job['budget_max'],
                ]
            );
        }        $this->command->info('Database seeded with Indonesian creative economy data:');
        $this->command->info('- ' . User::where('user_type', 'creative')->count() . ' creative professionals');
        $this->command->info('- ' . User::where('user_type', 'opportunity_owner')->count() . ' opportunity owners');
        $this->command->info('- ' . Job::count() . ' job postings');
        $this->command->info('- Covering sectors: Film & Animasi, Musik, Fashion, Kriya, Kuliner, Aplikasi & Game');

        // Re-enable Scout indexing
        config(['scout.driver' => $scoutEnabled]);
    }
}
