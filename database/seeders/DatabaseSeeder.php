<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\DisablesForeignKeys;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use DisablesForeignKeys;

    public function run(): void
    {
        $this->disableForeignKeys();

        $this->call([
            AdminSeeder::class,

            WebConfiguracionSitioSeeder::class,
            WebRedesSocialesSeeder::class,

            WebExpedidoSeeder::class,
            WebGradoAcademicoSeeder::class,

            WebCategoriaProgramaSeeder::class,
            WebEtiquetaSeeder::class,
            WebCifraInstitucionalSeeder::class,
            WebGaleriaCategoriaSeeder::class,
            WebFaqSeeder::class,
            WebHitoInstitucionalSeeder::class,

            CertificadoDemoSeeder::class,
        ]);

        $this->enableForeignKeys();
    }
}
