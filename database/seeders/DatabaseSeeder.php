<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Mitra;
use App\Models\Customer;
use App\Models\MessageService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

     public function customerSeeder()
    {
        $customers = [
            ['name' => 'Doni', 'address' => 'jl. gonjang ganjing no no.4'],
            ['name' => 'Linda', 'address' => 'Jl. kelapa gading'],
            ['name' => 'Danu', 'address' => 'Jl. Kebon anggur no 12'],
            ['name' => 'Tono', 'address' => 'Jl. Patimura'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate([
                'name' => $customer['name'],
                'address' => $customer['address'],
            ], [
                'name' => $customer['name']
            ]);
        }
    }

    public function mitraSeeder(){
        $mitras = [
            [
                'name' => 'Bobon spa',
                'address' => 'Depok, Sleman Regency, Special Region of Yogyakarta',
                'latitude' => '-7.775286',
                'longitude' => '110.379903',
            ],
            [
                'name' => 'Message spa 1',
                'address' => 'Depok, Sleman Regency, Special Region of Yogyakarta',
                'latitude' => '-7.782064',
                'longitude' => '110.403704',
            ],
            [
                'name' => 'Devina Message',
                'address' => 'Banguntapan, Bantul Regency, Special Region of Yogyakarta',
                'latitude' => '-7.790874',
                'longitude' => '110.411508',
            ],
            [
                'name' => 'Johan Message mlm',
                'address' => 'Kasihan, Bantul Regency, Special Region of Yogyakarta',
                'latitude' => '-7.810804',
                'longitude' => '110.321915',
            ],
            [
                'name' => 'Ibis Message Store',
                'address' => 'Sesek, Sidorejo, Blitar, East Java',
                'latitude' => '-7.985665',
                'longitude' => '112.176245',
            ],
            [
                'name' => 'Spa onTime',
                'address' => 'Baturetno, Patuk Kidul, Baturetno, Wonogiri Regency, Central Java',
                'latitude' => '-7.984154',
                'longitude' => '110.930823',
            ],
            [
                'name' => 'Spa onTime 2',
                'address' => 'Depok, Sleman Regency, Special Region of Yogyakarta',
                'latitude' => '-7.753658',
                'longitude' => '110.406563',
            ],
            [
                'name' => 'Spa Message 8',
                'address' => 'Sewon, Bantul Regency, Special Region of Yogyakarta',
                'latitude' => '-7.851724',
                'longitude' => '110.368116',
            ],
            [
                'name' => 'Message super',
                'address' => 'Wonogiri, Wonogiri Regency, Central Java',
                'latitude' => '-7.833136',
                'longitude' => '110.916985',
            ],
        ];

        foreach ($mitras as $mitra) {
            Mitra::updateOrCreate([
                'name' => $mitra['name'],
                'address' => $mitra['address'],
                'latitude' => $mitra['latitude'],
                'longitude' => $mitra['longitude']
            ], [
                'name' => $mitra['name']
            ]);
        }
    }

    public function messageServiceSeeder(){
        $datas = [
            [
                'name' => 'Pijat jaringan dalam',
                'description' => '-',
            ],
            [
                'name' => 'Pijat akupresur',
                'description' => '-'
            ],
            [
                'name' => 'Pijat Thai (Thai massage)',
                'description' => 'Pijat yang fokus pada titik bagian tubuh tertentu'
            ],
            [
                'name' => 'Pijat batu panas (hot stone massage)',
                'description' => 'pijat dengan terapi batu panas',
            ],
            [
                'name' => 'Pijat aromaterapi',
                'description' => 'Pijatan akan fokus pada area punggung, bahu, serta kepala, dan umumnya berlangsung selama 60–90 menit.'
            ]
        ];

        foreach ($datas as $data) {
            MessageService::updateOrCreate([
                'name' => $data['name'],
                'description' => $data['description']
            ], [
                'name' => $data['name']
            ]);
        }
    }

    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->customerSeeder();
        $this->mitraSeeder();
        $this->messageServiceSeeder();
        
    }
}
