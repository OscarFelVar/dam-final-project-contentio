<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\StateHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Agencia: Marcela (esposa) ────────────────────────────────────────
        $marcela = User::firstOrCreate(
            ['email' => 'marcelamotato.marketing@gmail.com'],
            [
                'name'     => 'Marcela Motato',
                'password' => Hash::make('Marcela2026!'),
                'role'     => 'agency',
            ]
        );

        // ─── Agencia: Felipe (Oscar) ──────────────────────────────────────────
        $felipe = User::firstOrCreate(
            ['email' => 'felipe.cvvargas@gmail.com'],
            [
                'name'     => 'Felipe Vargas',
                'password' => Hash::make('Felipe2026!'),
                'role'     => 'agency',
            ]
        );

        // ─── Clínicas de Marcela ──────────────────────────────────────────────
        $clinica1 = Clinic::firstOrCreate(
            ['name' => 'Clínica Belleza & Estética Madrid'],
            [
                'description'   => 'Centro especializado en tratamientos faciales y corporales.',
                'contact_email' => 'marcelamotato18@gmail.com',
                'user_id'       => $marcela->id,
            ]
        );

        $clinica2 = Clinic::firstOrCreate(
            ['name' => 'Spa & Wellness Premium'],
            [
                'description'   => 'Spa urbano con tratamientos de bienestar y relajación.',
                'contact_email' => 'info@spawellness.com',
                'user_id'       => $marcela->id,
            ]
        );

        $clinica3 = Clinic::firstOrCreate(
            ['name' => 'Centro Estético Glow'],
            [
                'description'   => 'Especialistas en medicina estética avanzada.',
                'contact_email' => 'hola@centroglow.com',
                'user_id'       => $marcela->id,
            ]
        );

        // ─── Usuario clinic en clínica 1 de Marcela (para probar Resend) ──────
        $clienteMarcela = User::firstOrCreate(
            ['email' => 'marcelamotato18@gmail.com'],
            [
                'name'     => 'Marcela (Cliente)',
                'password' => Hash::make('Cliente2026!'),
                'role'     => 'clinic',
            ]
        );
        $clinica1->clinicUsers()->syncWithoutDetaching([$clienteMarcela->id]);

        // ─── Clínica de Felipe ────────────────────────────────────────────────
        $clinicaFelipe = Clinic::firstOrCreate(
            ['name' => 'Clínica Demo Vargas'],
            [
                'description'   => 'Clínica de prueba para el flujo agencia-cliente.',
                'contact_email' => 'jhonnyvargas721@gmail.com',
                'user_id'       => $felipe->id,
            ]
        );

        // ─── Jhonny como usuario clinic de Felipe ─────────────────────────────
        $jhonny = User::firstOrCreate(
            ['email' => 'jhonnyvargas721@gmail.com'],
            [
                'name'     => 'Jhonny Vargas',
                'password' => Hash::make('Jhonny2026!'),
                'role'     => 'clinic',
            ]
        );
        $clinicaFelipe->clinicUsers()->syncWithoutDetaching([$jhonny->id]);

        // ─── Contenidos demo para el flujo Felipe → Jhonny ───────────────────
        $item1 = ContentItem::firstOrCreate(
            ['title' => 'Reel apertura clínica enero', 'clinic_id' => $clinicaFelipe->id],
            [
                'created_by'  => $felipe->id,
                'type'        => 'reel',
                'status'      => 'ready_for_review',
                'deadline_at' => '2026-05-10',
            ]
        );
        if ($item1->wasRecentlyCreated) {
            StateHistory::create([
                'content_item_id' => $item1->id,
                'user_id'         => $felipe->id,
                'from_status'     => 'draft',
                'to_status'       => 'ready_for_review',
            ]);
        }

        $item2 = ContentItem::firstOrCreate(
            ['title' => 'Post promoción primavera', 'clinic_id' => $clinicaFelipe->id],
            [
                'created_by'  => $felipe->id,
                'type'        => 'post',
                'status'      => 'draft',
                'deadline_at' => '2026-05-20',
            ]
        );

        // ─── Contenidos demo para Marcela ─────────────────────────────────────
        $item3 = ContentItem::firstOrCreate(
            ['title' => 'Reel tratamiento facial mayo', 'clinic_id' => $clinica1->id],
            [
                'created_by'  => $marcela->id,
                'type'        => 'reel',
                'status'      => 'ready_for_review',
                'deadline_at' => '2026-05-15',
            ]
        );
        if ($item3->wasRecentlyCreated) {
            StateHistory::create([
                'content_item_id' => $item3->id,
                'user_id'         => $marcela->id,
                'from_status'     => 'draft',
                'to_status'       => 'ready_for_review',
            ]);
        }

        ContentItem::firstOrCreate(
            ['title' => 'Story pack verano Glow', 'clinic_id' => $clinica3->id],
            [
                'created_by'  => $marcela->id,
                'type'        => 'story',
                'status'      => 'draft',
                'deadline_at' => '2026-06-01',
            ]
        );
    }
}
