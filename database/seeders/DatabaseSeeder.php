<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        fake()->seed(2025);
        // Usuarios
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $groupLeaders = \App\Models\User::factory(5)->groupLeader()->create();

        // Agentes de venta
        $salesAgents = \App\Models\SalesAgent::factory(5)->create();

        // Clientes
        $clients = \App\Models\Client::factory(5)->create();

        $plants = \App\Models\Plant::factory(5)->create();

        // Productos (3-5 por cliente)
        $products = collect();
        foreach ($clients as $client) {
            $products = $products->merge(
                \App\Models\Product::factory(rand(3, 5))->create([
                    'client_id' => $client->id,
                ])
            );
        }

        // Atributos personalizados
        $attributes = \App\Models\CustomAttribute::factory(8)->create();

        // Asignar atributos a productos (2-4 por producto)
        foreach ($products as $product) {
            $productAttrs = $attributes->random(rand(2, 4));
            foreach ($productAttrs as $attr) {
                DB::table('product_attributes')->insert([
                    'product_id' => $product->id,
                    'custom_attribute_id' => $attr->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Defectos y reworks
        $defects = \App\Models\Defect::factory(6)->create();
        $reworks = \App\Models\Rework::factory(4)->create();

        // Inspecciones (4-6 por planta)
        $inspections = collect();
        foreach ($plants as $plant) {
            $plantProducts = $products->where('client_id', $clients->random()->id);
            $inspections = $inspections->merge(
                \App\Models\Inspection::factory(rand(4, 6))->create([
                    'plant_id' => $plant->id,
                    'product_id' => $plantProducts->random()->id,
                    'group_leader_id' => $groupLeaders->random()->id
                ])
            );
        }

        // Lotes de inspección (3-5 por inspección)
        $lots = collect();
        foreach ($inspections as $inspection) {

            $inspection->salesAgents()->attach(
                $salesAgents->random(rand(0, 3))->pluck('id')->toArray()
            );

            $inspection->defects()->attach(
                $defects->random(rand(1, 3))->pluck('id')->toArray()
            );

            $inspection->reworks()->attach(
                $reworks->random(rand(1, 3))->pluck('id')->toArray()
            );

            $localLots = \App\Models\InspectionLot::factory(rand(3, 5))->create([
                'inspection_id' => $inspection->id,
            ]);

            foreach ($localLots as $lot) {
                $lotAttrs = $inspection->product->attributes;
                foreach ($lotAttrs as $attr) {
                    DB::table('inspection_lot_attributes')->insert([
                        'inspection_lot_id' => $lot->id,
                        'custom_attribute_id' => $attr->id,
                        'value' => fake()->word(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Instancias de defectos: elegir un subconjunto aleatorio de defectos asociados a la inspección, sin repetir
                $defectsForLot = $inspection->defects->random(rand(0, min(4, $inspection->defects->count())));
                foreach ($defectsForLot as $defect) {
                    \App\Models\DefectInstance::factory()->create([
                        'inspection_lot_id' => $lot->id,
                        'defect_id' => $defect->id,
                    ]);
                }

                // Instancias de reworks: igual que arriba, sin repetir combinación
                $reworksForLot = $inspection->reworks->random(rand(0, min(3, $inspection->reworks->count())));
                foreach ($reworksForLot as $rework) {
                    \App\Models\ReworkInstance::factory()->create([
                        'inspection_lot_id' => $lot->id,
                        'rework_id' => $rework->id,
                    ]);
                }
            }
            $lots->merge($localLots);
        }
    }
}
