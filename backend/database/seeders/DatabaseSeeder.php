<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\StateHistory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── Super Admin ──────────────────────────────────────────────────────
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@contentio.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        // ─── Agencia ──────────────────────────────────────────────────────────
        $agency = User::create([
            'name'     => 'Agencia Contentio',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'agency',
        ]);

        // ─── Clínicas ─────────────────────────────────────────────────────────
        $clinica1 = Clinic::create([
            'name'          => 'Clínica Estética Demo',
            'description'   => 'Clínica de referencia para pruebas de la aplicación.',
            'contact_email' => 'demo@clinica.com',
            'user_id'       => $agency->id,
        ]);

        $clinica2 = Clinic::create([
            'name'          => 'Belleza & Wellness Valencia',
            'description'   => 'Centro de bienestar y estética en Valencia.',
            'contact_email' => 'hola@bellezavalencia.com',
            'user_id'       => $agency->id,
        ]);

        // ─── Usuarios clinic ──────────────────────────────────────────────────
        $cliente1 = User::create([
            'name'     => 'Cliente Demo',
            'email'    => 'cliente@clinica.com',
            'password' => Hash::make('password123'),
            'role'     => 'clinic',
        ]);
        $clinica1->clinicUsers()->attach($cliente1->id);

        $cliente2 = User::create([
            'name'     => 'Ana García',
            'email'    => 'ana@bellezavalencia.com',
            'password' => Hash::make('password123'),
            'role'     => 'clinic',
        ]);
        $clinica2->clinicUsers()->attach($cliente2->id);

        // ─── Contenidos clínica 1 ─────────────────────────────────────────────
        $this->crearItem($agency, $clinica1, 'Reel apertura temporada verano', 'reel', 'draft', '2026-04-15');
        $this->crearItem($agency, $clinica1, 'Post tratamiento facial mayo', 'post', 'draft', '2026-05-01');

        $item3 = $this->crearItem($agency, $clinica1, 'Story promoción botox abril', 'story', 'ready_for_review', '2026-04-10');
        $this->addHistory($item3, $agency, 'draft', 'ready_for_review');

        $item4 = $this->crearItem($agency, $clinica1, 'Reel resultados mesoterapia', 'reel', 'approved', '2026-03-20');
        $this->addHistory($item4, $agency, 'draft', 'ready_for_review');
        $this->addHistory($item4, $cliente1, 'ready_for_review', 'approved');

        $item5 = $this->crearItem($agency, $clinica1, 'Post pack cumpleaños', 'post', 'in_correction', '2026-04-05');
        $this->addHistory($item5, $agency, 'draft', 'ready_for_review');
        $this->addHistory($item5, $cliente1, 'ready_for_review', 'in_correction');

        $item6 = $this->crearItem($agency, $clinica1, 'Story hidratación labial', 'story', 'rejected', '2026-03-25');
        $this->addHistory($item6, $agency, 'draft', 'ready_for_review');
        $this->addHistory($item6, $cliente1, 'ready_for_review', 'rejected');

        // ─── Contenidos clínica 2 ─────────────────────────────────────────────
        $this->crearItem($agency, $clinica2, 'Reel inauguración sala relax', 'reel', 'draft', '2026-04-20');

        $item8 = $this->crearItem($agency, $clinica2, 'Post masaje deportivo', 'post', 'ready_for_review', '2026-04-08');
        $this->addHistory($item8, $agency, 'draft', 'ready_for_review');

        $item9 = $this->crearItem($agency, $clinica2, 'Story pack primavera', 'story', 'approved', '2026-03-18');
        $this->addHistory($item9, $agency, 'draft', 'ready_for_review');
        $this->addHistory($item9, $cliente2, 'ready_for_review', 'approved');
    }

    private function crearItem(User $agency, Clinic $clinic, string $title, string $type, string $status, string $deadline): ContentItem
    {
        return ContentItem::create([
            'clinic_id'   => $clinic->id,
            'created_by'  => $agency->id,
            'title'       => $title,
            'type'        => $type,
            'status'      => $status,
            'deadline_at' => $deadline,
        ]);
    }

    private function addHistory(ContentItem $item, User $user, ?string $from, string $to): void
    {
        StateHistory::create([
            'content_item_id' => $item->id,
            'user_id'         => $user->id,
            'from_status'     => $from,
            'to_status'       => $to,
        ]);
    }
}
