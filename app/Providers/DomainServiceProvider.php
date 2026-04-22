<?php

namespace App\Providers;

use App\Domain\Ajustes\Contracts\AjusteRepositoryInterface;
use App\Domain\BannersPortal\Contracts\BannerPortalRepositoryInterface;
use App\Domain\CategoriasNoticia\Contracts\CategoriaNoticiaRepositoryInterface;
use App\Domain\ContactosMunicipales\Contracts\ContactoMunicipalRepositoryInterface;
use App\Domain\Etiquetas\Contracts\EtiquetaRepositoryInterface;
use App\Domain\Eventos\Contracts\EventoRepositoryInterface;
use App\Domain\EventosFotos\Contracts\EventoFotoRepositoryInterface;
use App\Domain\FormulariosTramite\Contracts\FormularioRepositoryInterface;
use App\Domain\MensajesContacto\Contracts\MensajeContactoRepositoryInterface;
use App\Domain\Noticias\Contracts\NoticiaRepositoryInterface;
use App\Domain\PreguntasFrecuentes\Contracts\PreguntaFrecuenteRepositoryInterface;
use App\Domain\RedesSociales\Contracts\RedSocialRepositoryInterface;
use App\Domain\RequisitosTramite\Contracts\RequisitoRepositoryInterface;
use App\Domain\Secretarias\Contracts\SecretariaRepositoryInterface;
use App\Domain\Subcenefcos\Contracts\SubcenefcoRepositoryInterface;
use App\Domain\SugerenciasReclamos\Contracts\SugerenciaReclamoRepositoryInterface;
use App\Domain\TiposEvento\Contracts\TipoEventoRepositoryInterface;
use App\Domain\TiposTramite\Contracts\TipoTramiteRepositoryInterface;
use App\Domain\TramitesCatalogo\Contracts\TramiteRepositoryInterface;
use App\Infrastructure\Ajustes\Repositories\EloquentAjusteRepository;
use App\Infrastructure\BannersPortal\Repositories\EloquentBannerPortalRepository;
use App\Infrastructure\CategoriasNoticia\Repositories\EloquentCategoriaNoticiaRepository;
use App\Infrastructure\ContactosMunicipales\Repositories\EloquentContactoMunicipalRepository;
use App\Infrastructure\Etiquetas\Repositories\EloquentEtiquetaRepository;
use App\Infrastructure\Eventos\Repositories\EloquentEventoRepository;
use App\Infrastructure\EventosFotos\Repositories\EloquentEventoFotoRepository;
use App\Infrastructure\FormulariosTramite\Repositories\EloquentFormularioRepository;
use App\Infrastructure\MensajesContacto\Repositories\EloquentMensajeContactoRepository;
use App\Infrastructure\Noticias\Repositories\EloquentNoticiaRepository;
use App\Infrastructure\PreguntasFrecuentes\Repositories\EloquentPreguntaFrecuenteRepository;
use App\Infrastructure\RedesSociales\Repositories\EloquentRedSocialRepository;
use App\Infrastructure\RequisitosTramite\Repositories\EloquentRequisitoRepository;
use App\Infrastructure\Secretarias\Repositories\EloquentSecretariaRepository;
use App\Infrastructure\Subcenefcos\Repositories\EloquentSubcenefcoRepository;
use App\Infrastructure\SugerenciasReclamos\Repositories\EloquentSugerenciaReclamoRepository;
use App\Infrastructure\TiposEvento\Repositories\EloquentTipoEventoRepository;
use App\Infrastructure\TiposTramite\Repositories\EloquentTipoTramiteRepository;
use App\Infrastructure\TramitesCatalogo\Repositories\EloquentTramiteRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RedSocialRepositoryInterface::class, EloquentRedSocialRepository::class);
        $this->app->bind(CategoriaNoticiaRepositoryInterface::class, EloquentCategoriaNoticiaRepository::class);
        $this->app->bind(EtiquetaRepositoryInterface::class, EloquentEtiquetaRepository::class);
        $this->app->bind(NoticiaRepositoryInterface::class, EloquentNoticiaRepository::class);
        $this->app->bind(SecretariaRepositoryInterface::class, EloquentSecretariaRepository::class);
        $this->app->bind(SubcenefcoRepositoryInterface::class, EloquentSubcenefcoRepository::class);

        $this->app->bind(TipoTramiteRepositoryInterface::class, EloquentTipoTramiteRepository::class);
        $this->app->bind(TramiteRepositoryInterface::class, EloquentTramiteRepository::class);
        $this->app->bind(RequisitoRepositoryInterface::class, EloquentRequisitoRepository::class);
        $this->app->bind(FormularioRepositoryInterface::class, EloquentFormularioRepository::class);

        $this->app->bind(TipoEventoRepositoryInterface::class, EloquentTipoEventoRepository::class);
        $this->app->bind(EventoRepositoryInterface::class, EloquentEventoRepository::class);
        $this->app->bind(EventoFotoRepositoryInterface::class, EloquentEventoFotoRepository::class);

        $this->app->bind(SugerenciaReclamoRepositoryInterface::class, EloquentSugerenciaReclamoRepository::class);
        $this->app->bind(ContactoMunicipalRepositoryInterface::class, EloquentContactoMunicipalRepository::class);
        $this->app->bind(MensajeContactoRepositoryInterface::class, EloquentMensajeContactoRepository::class);
        $this->app->bind(PreguntaFrecuenteRepositoryInterface::class, EloquentPreguntaFrecuenteRepository::class);

        $this->app->bind(AjusteRepositoryInterface::class, EloquentAjusteRepository::class);
        $this->app->bind(BannerPortalRepositoryInterface::class, EloquentBannerPortalRepository::class);

        $this->app->bind(\App\Domain\Usuarios\Contracts\UserRepositoryInterface::class, \App\Infrastructure\Usuarios\Repositories\EloquentUserRepository::class);
        $this->app->bind(\App\Domain\Usuarios\Contracts\RoleRepositoryInterface::class, \App\Infrastructure\Usuarios\Repositories\EloquentRoleRepository::class);

        $this->app->bind(\App\Domain\Comunicados\Contracts\ComunicadoRepositoryInterface::class, \App\Infrastructure\Comunicados\Repositories\EloquentComunicadoRepository::class);

        $this->app->bind(\App\Domain\Permisos\Contracts\PermisoRepositoryInterface::class, \App\Infrastructure\Permisos\Repositories\EloquentPermisoRepository::class);

        $this->app->bind(
            \App\Domain\Moodle\Contracts\MoodleCourseRepositoryInterface::class,
            \App\Infrastructure\Moodle\Repositories\MoodleCourseRepository::class
        );
    }
}
