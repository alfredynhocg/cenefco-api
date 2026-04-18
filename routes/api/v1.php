<?php

use App\Http\Controllers\Api\AjusteController;
use App\Http\Controllers\Api\BannerPortalController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EtiquetaController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\MensajeContactoController;
use App\Http\Controllers\Api\RedSocialController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\WhatsAppAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'stats']);

    Route::post('/upload/image', [UploadController::class, 'image']);
    Route::post('/upload/file', [UploadController::class, 'file']);

    Route::get('/redes-sociales', [RedSocialController::class, 'index']);
    Route::post('/redes-sociales', [RedSocialController::class, 'store']);
    Route::get('/redes-sociales/{id}', [RedSocialController::class, 'show']);
    Route::put('/redes-sociales/{id}', [RedSocialController::class, 'update']);
    Route::delete('/redes-sociales/{id}', [RedSocialController::class, 'destroy']);

    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permiso:usuarios.crear');
    Route::put('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('permiso:usuarios.editar');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
        ->middleware('permiso:usuarios.eliminar');

    Route::get('/etiquetas', [EtiquetaController::class, 'index']);
    Route::post('/etiquetas', [EtiquetaController::class, 'store'])
        ->middleware('permiso:noticias.crear');
    Route::get('/etiquetas/{id}', [EtiquetaController::class, 'show']);
    Route::put('/etiquetas/{id}', [EtiquetaController::class, 'update'])
        ->middleware('permiso:noticias.editar');
    Route::delete('/etiquetas/{id}', [EtiquetaController::class, 'destroy'])
        ->middleware('permiso:noticias.eliminar');

    Route::get('/eventos', [EventoController::class, 'index']);
    Route::post('/eventos', [EventoController::class, 'store'])
        ->middleware('permiso:eventos.crear');
    Route::get('/eventos/{id}', [EventoController::class, 'show']);
    Route::put('/eventos/{id}', [EventoController::class, 'update'])
        ->middleware('permiso:eventos.editar');
    Route::delete('/eventos/{id}', [EventoController::class, 'destroy'])
        ->middleware('permiso:eventos.eliminar');

    Route::get('/preguntas-frecuentes', [\App\Http\Controllers\Api\PreguntaFrecuenteController::class, 'index'])
        ->middleware('permiso:contenido.ver');
    Route::post('/preguntas-frecuentes', [\App\Http\Controllers\Api\PreguntaFrecuenteController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/preguntas-frecuentes/{id}', [\App\Http\Controllers\Api\PreguntaFrecuenteController::class, 'show'])
        ->middleware('permiso:contenido.ver');
    Route::put('/preguntas-frecuentes/{id}', [\App\Http\Controllers\Api\PreguntaFrecuenteController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/preguntas-frecuentes/{id}', [\App\Http\Controllers\Api\PreguntaFrecuenteController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/mensajes-contacto', [MensajeContactoController::class, 'index'])
        ->middleware('permiso:contacto.ver');
    Route::post('/mensajes-contacto', [MensajeContactoController::class, 'store']);
    Route::get('/mensajes-contacto/{id}', [MensajeContactoController::class, 'show'])
        ->middleware('permiso:contacto.ver');
    Route::post('/mensajes-contacto/{id}/responder', [MensajeContactoController::class, 'respond'])
        ->middleware('permiso:contacto.editar');
    Route::delete('/mensajes-contacto/{id}', [MensajeContactoController::class, 'destroy'])
        ->middleware('permiso:contacto.eliminar');

    Route::get('/ajustes', [AjusteController::class, 'index']);
    Route::get('/ajustes/{key}', [AjusteController::class, 'show']);
    Route::put('/ajustes/{key}', [AjusteController::class, 'update'])
        ->middleware('permiso:configuracion.editar');

    Route::get('/banners-portal', [BannerPortalController::class, 'index']);
    Route::post('/banners-portal', [BannerPortalController::class, 'store'])
        ->middleware('permiso:configuracion.editar');
    Route::get('/banners-portal/{id}', [BannerPortalController::class, 'show']);
    Route::put('/banners-portal/{id}', [BannerPortalController::class, 'update'])
        ->middleware('permiso:configuracion.editar');
    Route::delete('/banners-portal/{id}', [BannerPortalController::class, 'destroy'])
        ->middleware('permiso:configuracion.eliminar');

    Route::get('/asesores', [\App\Http\Controllers\Api\AsesorController::class, 'index']);
    Route::post('/asesores', [\App\Http\Controllers\Api\AsesorController::class, 'store']);
    Route::get('/asesores/{id}', [\App\Http\Controllers\Api\AsesorController::class, 'show']);
    Route::put('/asesores/{id}', [\App\Http\Controllers\Api\AsesorController::class, 'update']);
    Route::delete('/asesores/{id}', [\App\Http\Controllers\Api\AsesorController::class, 'destroy']);
    Route::post('/whatsapp/conversaciones/{id}/asesor', [\App\Http\Controllers\Api\AsesorController::class, 'asignar']);

    Route::get('/whatsapp/conversaciones', [WhatsAppAdminController::class, 'conversaciones']);
    Route::get('/whatsapp/conversaciones/{id}/mensajes', [WhatsAppAdminController::class, 'mensajes']);
    Route::post('/whatsapp/conversaciones/{id}/etiquetas', [WhatsAppAdminController::class, 'asignarEtiquetas']);
    Route::get('/whatsapp/phones', [WhatsAppAdminController::class, 'todosPhones']);
    Route::post('/whatsapp/conversaciones/{id}/atendido', [WhatsAppAdminController::class, 'marcarAtendido']);
    Route::post('/whatsapp/enviar', [WhatsAppAdminController::class, 'enviar']);
    Route::post('/whatsapp/enviar-masivo', [WhatsAppAdminController::class, 'enviarMasivo']);
    Route::post('/whatsapp/enviar-media', [WhatsAppAdminController::class, 'enviarMedia']);
    Route::post('/whatsapp/plantilla', [WhatsAppAdminController::class, 'enviarPlantilla']);
    Route::get('/whatsapp/etiquetas', [WhatsAppAdminController::class, 'etiquetas']);
    Route::post('/whatsapp/etiquetas', [WhatsAppAdminController::class, 'crearEtiqueta']);
    Route::put('/whatsapp/etiquetas/{id}', [WhatsAppAdminController::class, 'actualizarEtiqueta']);
    Route::delete('/whatsapp/etiquetas/{id}', [WhatsAppAdminController::class, 'eliminarEtiqueta']);

    Route::get('/cursos', [\App\Http\Controllers\Api\CursoController::class, 'index']);
    Route::post('/cursos', [\App\Http\Controllers\Api\CursoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/cursos/{id}', [\App\Http\Controllers\Api\CursoController::class, 'show']);
    Route::put('/cursos/{id}', [\App\Http\Controllers\Api\CursoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/cursos/{id}', [\App\Http\Controllers\Api\CursoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/categorias-programa', [\App\Http\Controllers\Api\CategoriaProgramaController::class, 'index']);
    Route::post('/categorias-programa', [\App\Http\Controllers\Api\CategoriaProgramaController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/categorias-programa/{id}', [\App\Http\Controllers\Api\CategoriaProgramaController::class, 'show']);
    Route::put('/categorias-programa/{id}', [\App\Http\Controllers\Api\CategoriaProgramaController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/categorias-programa/{id}', [\App\Http\Controllers\Api\CategoriaProgramaController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/tipos-programa', [\App\Http\Controllers\Api\TipoProgramaController::class, 'index']);

    Route::get('/preinscripciones', [\App\Http\Controllers\Api\PreinscripcionController::class, 'index'])
        ->middleware('permiso:inscripciones.ver');
    Route::get('/preinscripciones/{id}', [\App\Http\Controllers\Api\PreinscripcionController::class, 'show'])
        ->middleware('permiso:inscripciones.ver');
    Route::put('/preinscripciones/{id}', [\App\Http\Controllers\Api\PreinscripcionController::class, 'update'])
        ->middleware('permiso:inscripciones.editar');
    Route::delete('/preinscripciones/{id}', [\App\Http\Controllers\Api\PreinscripcionController::class, 'destroy'])
        ->middleware('permiso:inscripciones.eliminar');

    Route::get('/resenas', [\App\Http\Controllers\Api\ResenaController::class, 'index'])
        ->middleware('permiso:contenido.ver');
    Route::get('/resenas/{id}', [\App\Http\Controllers\Api\ResenaController::class, 'show'])
        ->middleware('permiso:contenido.ver');
    Route::put('/resenas/{id}', [\App\Http\Controllers\Api\ResenaController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/resenas/{id}', [\App\Http\Controllers\Api\ResenaController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/faqs', [\App\Http\Controllers\Api\FaqController::class, 'index']);
    Route::post('/faqs', [\App\Http\Controllers\Api\FaqController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/faqs/{id}', [\App\Http\Controllers\Api\FaqController::class, 'show']);
    Route::put('/faqs/{id}', [\App\Http\Controllers\Api\FaqController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/faqs/{id}', [\App\Http\Controllers\Api\FaqController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/cifras-institucionales', [\App\Http\Controllers\Api\CifraInstitucionalController::class, 'index']);
    Route::post('/cifras-institucionales', [\App\Http\Controllers\Api\CifraInstitucionalController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/cifras-institucionales/{id}', [\App\Http\Controllers\Api\CifraInstitucionalController::class, 'show']);
    Route::put('/cifras-institucionales/{id}', [\App\Http\Controllers\Api\CifraInstitucionalController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/cifras-institucionales/{id}', [\App\Http\Controllers\Api\CifraInstitucionalController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/hitos-institucionales', [\App\Http\Controllers\Api\HitoInstitucionalController::class, 'index']);
    Route::post('/hitos-institucionales', [\App\Http\Controllers\Api\HitoInstitucionalController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/hitos-institucionales/{id}', [\App\Http\Controllers\Api\HitoInstitucionalController::class, 'show']);
    Route::put('/hitos-institucionales/{id}', [\App\Http\Controllers\Api\HitoInstitucionalController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/hitos-institucionales/{id}', [\App\Http\Controllers\Api\HitoInstitucionalController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/expedido', [\App\Http\Controllers\Api\ExpedidoController::class, 'index']);
    Route::post('/expedido', [\App\Http\Controllers\Api\ExpedidoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/expedido/{id}', [\App\Http\Controllers\Api\ExpedidoController::class, 'show']);
    Route::put('/expedido/{id}', [\App\Http\Controllers\Api\ExpedidoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/expedido/{id}', [\App\Http\Controllers\Api\ExpedidoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/grados-academicos', [\App\Http\Controllers\Api\GradoAcademicoController::class, 'index']);
    Route::post('/grados-academicos', [\App\Http\Controllers\Api\GradoAcademicoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/grados-academicos/{id}', [\App\Http\Controllers\Api\GradoAcademicoController::class, 'show']);
    Route::put('/grados-academicos/{id}', [\App\Http\Controllers\Api\GradoAcademicoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/grados-academicos/{id}', [\App\Http\Controllers\Api\GradoAcademicoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/config-sitio', [\App\Http\Controllers\Api\ConfigSitioController::class, 'show']);
    Route::put('/config-sitio', [\App\Http\Controllers\Api\ConfigSitioController::class, 'update'])
        ->middleware('permiso:transparencia.crear');

    Route::get('/permisos', [\App\Http\Controllers\Api\PermisoController::class, 'index'])
        ->middleware('permiso:usuarios.ver');
    Route::post('/permisos', [\App\Http\Controllers\Api\PermisoController::class, 'store'])
        ->middleware('permiso:usuarios.crear');
    Route::get('/permisos/{id}', [\App\Http\Controllers\Api\PermisoController::class, 'show'])
        ->middleware('permiso:usuarios.ver');
    Route::put('/permisos/{id}', [\App\Http\Controllers\Api\PermisoController::class, 'update'])
        ->middleware('permiso:usuarios.editar');
    Route::delete('/permisos/{id}', [\App\Http\Controllers\Api\PermisoController::class, 'destroy'])
        ->middleware('permiso:usuarios.eliminar');

    Route::get('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
    Route::put('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'update'])
        ->middleware('permiso:configuracion.editar');

    Route::get('/suscriptores', [\App\Http\Controllers\Api\SuscriptorController::class, 'index'])
        ->middleware('permiso:contenido.ver');
    Route::post('/suscriptores', [\App\Http\Controllers\Api\SuscriptorController::class, 'store']);
    Route::get('/suscriptores/{id}', [\App\Http\Controllers\Api\SuscriptorController::class, 'show'])
        ->middleware('permiso:contenido.ver');
    Route::put('/suscriptores/{id}', [\App\Http\Controllers\Api\SuscriptorController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/suscriptores/{id}', [\App\Http\Controllers\Api\SuscriptorController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/testimonios', [\App\Http\Controllers\Api\TestimonioController::class, 'index']);
    Route::post('/testimonios', [\App\Http\Controllers\Api\TestimonioController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/testimonios/{id}', [\App\Http\Controllers\Api\TestimonioController::class, 'show']);
    Route::put('/testimonios/{id}', [\App\Http\Controllers\Api\TestimonioController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/testimonios/{id}', [\App\Http\Controllers\Api\TestimonioController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/aliados', [\App\Http\Controllers\Api\AliadoController::class, 'index']);
    Route::post('/aliados', [\App\Http\Controllers\Api\AliadoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/aliados/{id}', [\App\Http\Controllers\Api\AliadoController::class, 'show']);
    Route::put('/aliados/{id}', [\App\Http\Controllers\Api\AliadoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/aliados/{id}', [\App\Http\Controllers\Api\AliadoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/docentes-perfil', [\App\Http\Controllers\Api\DocentePerfilController::class, 'index']);
    Route::post('/docentes-perfil', [\App\Http\Controllers\Api\DocentePerfilController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/docentes-perfil/{id}', [\App\Http\Controllers\Api\DocentePerfilController::class, 'show']);
    Route::put('/docentes-perfil/{id}', [\App\Http\Controllers\Api\DocentePerfilController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/docentes-perfil/{id}', [\App\Http\Controllers\Api\DocentePerfilController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/popups', [\App\Http\Controllers\Api\PopupController::class, 'index']);
    Route::post('/popups', [\App\Http\Controllers\Api\PopupController::class, 'store'])
        ->middleware('permiso:configuracion.editar');
    Route::get('/popups/{id}', [\App\Http\Controllers\Api\PopupController::class, 'show']);
    Route::put('/popups/{id}', [\App\Http\Controllers\Api\PopupController::class, 'update'])
        ->middleware('permiso:configuracion.editar');
    Route::delete('/popups/{id}', [\App\Http\Controllers\Api\PopupController::class, 'destroy'])
        ->middleware('permiso:configuracion.eliminar');

    Route::get('/whatsapp-grupos', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'index']);
    Route::post('/whatsapp-grupos', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/whatsapp-grupos/{id}', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'show']);
    Route::put('/whatsapp-grupos/{id}', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/whatsapp-grupos/{id}', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/galeria-categorias', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'index']);
    Route::post('/galeria-categorias', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/galeria-categorias/{id}', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'show']);
    Route::put('/galeria-categorias/{id}', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/galeria-categorias/{id}', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/acreditaciones', [\App\Http\Controllers\Api\AcreditacionController::class, 'index']);
    Route::post('/acreditaciones', [\App\Http\Controllers\Api\AcreditacionController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/acreditaciones/{id}', [\App\Http\Controllers\Api\AcreditacionController::class, 'show']);
    Route::put('/acreditaciones/{id}', [\App\Http\Controllers\Api\AcreditacionController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/acreditaciones/{id}', [\App\Http\Controllers\Api\AcreditacionController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/notas-prensa', [\App\Http\Controllers\Api\NotaPrensaController::class, 'index']);
    Route::post('/notas-prensa', [\App\Http\Controllers\Api\NotaPrensaController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/notas-prensa/{id}', [\App\Http\Controllers\Api\NotaPrensaController::class, 'show']);
    Route::put('/notas-prensa/{id}', [\App\Http\Controllers\Api\NotaPrensaController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/notas-prensa/{id}', [\App\Http\Controllers\Api\NotaPrensaController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/descargables', [\App\Http\Controllers\Api\DescargableController::class, 'index']);
    Route::post('/descargables', [\App\Http\Controllers\Api\DescargableController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/descargables/{id}', [\App\Http\Controllers\Api\DescargableController::class, 'show']);
    Route::put('/descargables/{id}', [\App\Http\Controllers\Api\DescargableController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/descargables/{id}', [\App\Http\Controllers\Api\DescargableController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/galeria-videos', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'index']);
    Route::post('/galeria-videos', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/galeria-videos/{id}', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'show']);
    Route::put('/galeria-videos/{id}', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/galeria-videos/{id}', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/calendario-academico', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'index']);
    Route::post('/calendario-academico', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/calendario-academico/{id}', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'show']);
    Route::put('/calendario-academico/{id}', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/calendario-academico/{id}', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/redirecciones', [\App\Http\Controllers\Api\RedireccionController::class, 'index'])
        ->middleware('permiso:configuracion.editar');
    Route::post('/redirecciones', [\App\Http\Controllers\Api\RedireccionController::class, 'store'])
        ->middleware('permiso:configuracion.editar');
    Route::get('/redirecciones/{id}', [\App\Http\Controllers\Api\RedireccionController::class, 'show'])
        ->middleware('permiso:configuracion.editar');
    Route::put('/redirecciones/{id}', [\App\Http\Controllers\Api\RedireccionController::class, 'update'])
        ->middleware('permiso:configuracion.editar');
    Route::delete('/redirecciones/{id}', [\App\Http\Controllers\Api\RedireccionController::class, 'destroy'])
        ->middleware('permiso:configuracion.eliminar');

    Route::get('/descuentos-promociones', [\App\Http\Controllers\Api\DescuentoPromocionController::class, 'index']);
    Route::post('/descuentos-promociones', [\App\Http\Controllers\Api\DescuentoPromocionController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/descuentos-promociones/{id}', [\App\Http\Controllers\Api\DescuentoPromocionController::class, 'show']);
    Route::put('/descuentos-promociones/{id}', [\App\Http\Controllers\Api\DescuentoPromocionController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/descuentos-promociones/{id}', [\App\Http\Controllers\Api\DescuentoPromocionController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/cert-plantillas', [\App\Http\Controllers\Api\CertPlantillaController::class, 'index']);
    Route::post('/cert-plantillas', [\App\Http\Controllers\Api\CertPlantillaController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/cert-plantillas/{id}', [\App\Http\Controllers\Api\CertPlantillaController::class, 'show']);
    Route::put('/cert-plantillas/{id}', [\App\Http\Controllers\Api\CertPlantillaController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/cert-plantillas/{id}', [\App\Http\Controllers\Api\CertPlantillaController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/cert-plantilla-campos', [\App\Http\Controllers\Api\CertPlantillaCampoController::class, 'index']);
    Route::post('/cert-plantilla-campos', [\App\Http\Controllers\Api\CertPlantillaCampoController::class, 'store'])
        ->middleware('permiso:contenido.crear');
    Route::get('/cert-plantilla-campos/{id}', [\App\Http\Controllers\Api\CertPlantillaCampoController::class, 'show']);
    Route::put('/cert-plantilla-campos/{id}', [\App\Http\Controllers\Api\CertPlantillaCampoController::class, 'update'])
        ->middleware('permiso:contenido.editar');
    Route::delete('/cert-plantilla-campos/{id}', [\App\Http\Controllers\Api\CertPlantillaCampoController::class, 'destroy'])
        ->middleware('permiso:contenido.eliminar');

    Route::get('/lista-aprobados', [\App\Http\Controllers\Api\ListaAprobadosController::class, 'index'])
        ->middleware('permiso:inscripciones.ver');
    Route::post('/lista-aprobados', [\App\Http\Controllers\Api\ListaAprobadosController::class, 'store'])
        ->middleware('permiso:inscripciones.editar');
    Route::get('/lista-aprobados/{id}', [\App\Http\Controllers\Api\ListaAprobadosController::class, 'show'])
        ->middleware('permiso:inscripciones.ver');
    Route::put('/lista-aprobados/{id}', [\App\Http\Controllers\Api\ListaAprobadosController::class, 'update'])
        ->middleware('permiso:inscripciones.editar');
    Route::delete('/lista-aprobados/{id}', [\App\Http\Controllers\Api\ListaAprobadosController::class, 'destroy'])
        ->middleware('permiso:inscripciones.eliminar');

    Route::get('/certificados', [\App\Http\Controllers\Api\CertificadoController::class, 'index'])
        ->middleware('permiso:inscripciones.ver');
    Route::post('/certificados', [\App\Http\Controllers\Api\CertificadoController::class, 'store'])
        ->middleware('permiso:inscripciones.editar');
    Route::get('/certificados/codigo/{codigo}', [\App\Http\Controllers\Api\CertificadoController::class, 'showByCode']);
    Route::get('/certificados/{id}', [\App\Http\Controllers\Api\CertificadoController::class, 'show']);
    Route::put('/certificados/{id}', [\App\Http\Controllers\Api\CertificadoController::class, 'update'])
        ->middleware('permiso:inscripciones.editar');
    Route::delete('/certificados/{id}', [\App\Http\Controllers\Api\CertificadoController::class, 'destroy'])
        ->middleware('permiso:inscripciones.eliminar');

    Route::get('/cert-verificaciones', [\App\Http\Controllers\Api\CertVerificacionController::class, 'index'])
        ->middleware('permiso:inscripciones.ver');
    Route::post('/cert-verificaciones', [\App\Http\Controllers\Api\CertVerificacionController::class, 'store']);
    Route::get('/cert-verificaciones/{id}', [\App\Http\Controllers\Api\CertVerificacionController::class, 'show'])
        ->middleware('permiso:inscripciones.ver');

    Route::get('/carreras', [\App\Http\Controllers\Api\CarreraController::class, 'index']);
    Route::post('/carreras', [\App\Http\Controllers\Api\CarreraController::class, 'store']);
    Route::get('/carreras/{id}', [\App\Http\Controllers\Api\CarreraController::class, 'show']);
    Route::put('/carreras/{id}', [\App\Http\Controllers\Api\CarreraController::class, 'update']);
    Route::delete('/carreras/{id}', [\App\Http\Controllers\Api\CarreraController::class, 'destroy']);

    Route::get('/materias', [\App\Http\Controllers\Api\MateriaController::class, 'index']);
    Route::post('/materias', [\App\Http\Controllers\Api\MateriaController::class, 'store']);
    Route::get('/materias/{id}', [\App\Http\Controllers\Api\MateriaController::class, 'show']);
    Route::put('/materias/{id}', [\App\Http\Controllers\Api\MateriaController::class, 'update']);
    Route::delete('/materias/{id}', [\App\Http\Controllers\Api\MateriaController::class, 'destroy']);

    Route::get('/usuarios-academicos', [\App\Http\Controllers\Api\UsuarioAcademicoController::class, 'index']);
    Route::post('/usuarios-academicos', [\App\Http\Controllers\Api\UsuarioAcademicoController::class, 'store']);
    Route::get('/usuarios-academicos/{id}', [\App\Http\Controllers\Api\UsuarioAcademicoController::class, 'show']);
    Route::put('/usuarios-academicos/{id}', [\App\Http\Controllers\Api\UsuarioAcademicoController::class, 'update']);
    Route::delete('/usuarios-academicos/{id}', [\App\Http\Controllers\Api\UsuarioAcademicoController::class, 'destroy']);

    Route::get('/imparticiones', [\App\Http\Controllers\Api\ImparteController::class, 'index']);
    Route::post('/imparticiones', [\App\Http\Controllers\Api\ImparteController::class, 'store']);
    Route::get('/imparticiones/{id}', [\App\Http\Controllers\Api\ImparteController::class, 'show']);
    Route::put('/imparticiones/{id}', [\App\Http\Controllers\Api\ImparteController::class, 'update']);
    Route::delete('/imparticiones/{id}', [\App\Http\Controllers\Api\ImparteController::class, 'destroy']);

    Route::get('/inscripciones', [\App\Http\Controllers\Api\InscripcionController::class, 'index']);
    Route::post('/inscripciones', [\App\Http\Controllers\Api\InscripcionController::class, 'store']);
    Route::get('/inscripciones/{id}', [\App\Http\Controllers\Api\InscripcionController::class, 'show']);
    Route::put('/inscripciones/{id}', [\App\Http\Controllers\Api\InscripcionController::class, 'update']);
    Route::delete('/inscripciones/{id}', [\App\Http\Controllers\Api\InscripcionController::class, 'destroy']);

    Route::get('/notas-academicas', [\App\Http\Controllers\Api\NotaController::class, 'index']);
    Route::post('/notas-academicas', [\App\Http\Controllers\Api\NotaController::class, 'store']);
    Route::get('/notas-academicas/{id}', [\App\Http\Controllers\Api\NotaController::class, 'show']);
    Route::put('/notas-academicas/{id}', [\App\Http\Controllers\Api\NotaController::class, 'update']);
    Route::delete('/notas-academicas/{id}', [\App\Http\Controllers\Api\NotaController::class, 'destroy']);

    Route::get('/pagos-academicos', [\App\Http\Controllers\Api\PagoController::class, 'index']);
    Route::post('/pagos-academicos', [\App\Http\Controllers\Api\PagoController::class, 'store']);
    Route::get('/pagos-academicos/{id}', [\App\Http\Controllers\Api\PagoController::class, 'show']);
    Route::put('/pagos-academicos/{id}', [\App\Http\Controllers\Api\PagoController::class, 'update']);
    Route::delete('/pagos-academicos/{id}', [\App\Http\Controllers\Api\PagoController::class, 'destroy']);

    Route::get('/horarios-academicos', [\App\Http\Controllers\Api\HorarioController::class, 'index']);
    Route::post('/horarios-academicos', [\App\Http\Controllers\Api\HorarioController::class, 'store']);
    Route::get('/horarios-academicos/{id}', [\App\Http\Controllers\Api\HorarioController::class, 'show']);
    Route::put('/horarios-academicos/{id}', [\App\Http\Controllers\Api\HorarioController::class, 'update']);
    Route::delete('/horarios-academicos/{id}', [\App\Http\Controllers\Api\HorarioController::class, 'destroy']);

    Route::get('/planes-academicos', [\App\Http\Controllers\Api\PlanAcademicoController::class, 'index']);
    Route::post('/planes-academicos', [\App\Http\Controllers\Api\PlanAcademicoController::class, 'store']);
    Route::get('/planes-academicos/{id}', [\App\Http\Controllers\Api\PlanAcademicoController::class, 'show']);
    Route::put('/planes-academicos/{id}', [\App\Http\Controllers\Api\PlanAcademicoController::class, 'update']);
    Route::delete('/planes-academicos/{id}', [\App\Http\Controllers\Api\PlanAcademicoController::class, 'destroy']);

    Route::get('/catalogo-academico/{catalogo}', [\App\Http\Controllers\Api\CatalogoAcademicoController::class, 'index']);
    Route::post('/catalogo-academico/{catalogo}', [\App\Http\Controllers\Api\CatalogoAcademicoController::class, 'store']);
    Route::get('/catalogo-academico/{catalogo}/{id}', [\App\Http\Controllers\Api\CatalogoAcademicoController::class, 'show']);
    Route::put('/catalogo-academico/{catalogo}/{id}', [\App\Http\Controllers\Api\CatalogoAcademicoController::class, 'update']);
    Route::delete('/catalogo-academico/{catalogo}/{id}', [\App\Http\Controllers\Api\CatalogoAcademicoController::class, 'destroy']);

    Route::get('/articulos', [\App\Http\Controllers\Api\ArticuloController::class, 'index']);
    Route::post('/articulos', [\App\Http\Controllers\Api\ArticuloController::class, 'store']);
    Route::get('/articulos/{id}', [\App\Http\Controllers\Api\ArticuloController::class, 'show']);
    Route::put('/articulos/{id}', [\App\Http\Controllers\Api\ArticuloController::class, 'update']);
    Route::delete('/articulos/{id}', [\App\Http\Controllers\Api\ArticuloController::class, 'destroy']);

    Route::get('/boletines', [\App\Http\Controllers\Api\BoletinController::class, 'index']);
    Route::post('/boletines', [\App\Http\Controllers\Api\BoletinController::class, 'store']);
    Route::get('/boletines/{id}', [\App\Http\Controllers\Api\BoletinController::class, 'show']);
    Route::put('/boletines/{id}', [\App\Http\Controllers\Api\BoletinController::class, 'update']);
    Route::delete('/boletines/{id}', [\App\Http\Controllers\Api\BoletinController::class, 'destroy']);

    Route::get('/fotos', [\App\Http\Controllers\Api\FotoController::class, 'index']);
    Route::post('/fotos', [\App\Http\Controllers\Api\FotoController::class, 'store']);
    Route::get('/fotos/{id}', [\App\Http\Controllers\Api\FotoController::class, 'show']);
    Route::put('/fotos/{id}', [\App\Http\Controllers\Api\FotoController::class, 'update']);
    Route::delete('/fotos/{id}', [\App\Http\Controllers\Api\FotoController::class, 'destroy']);

    Route::get('/tesis', [\App\Http\Controllers\Api\TesisController::class, 'index']);
    Route::post('/tesis', [\App\Http\Controllers\Api\TesisController::class, 'store']);
    Route::get('/tesis/{id}', [\App\Http\Controllers\Api\TesisController::class, 'show']);
    Route::put('/tesis/{id}', [\App\Http\Controllers\Api\TesisController::class, 'update']);
    Route::delete('/tesis/{id}', [\App\Http\Controllers\Api\TesisController::class, 'destroy']);

    Route::get('/monografias', [\App\Http\Controllers\Api\MonografiaController::class, 'index']);
    Route::post('/monografias', [\App\Http\Controllers\Api\MonografiaController::class, 'store']);
    Route::get('/monografias/{id}', [\App\Http\Controllers\Api\MonografiaController::class, 'show']);
    Route::put('/monografias/{id}', [\App\Http\Controllers\Api\MonografiaController::class, 'update']);
    Route::delete('/monografias/{id}', [\App\Http\Controllers\Api\MonografiaController::class, 'destroy']);

    Route::get('/revistas', [\App\Http\Controllers\Api\RevistaController::class, 'index']);
    Route::post('/revistas', [\App\Http\Controllers\Api\RevistaController::class, 'store']);
    Route::get('/revistas/{id}', [\App\Http\Controllers\Api\RevistaController::class, 'show']);
    Route::put('/revistas/{id}', [\App\Http\Controllers\Api\RevistaController::class, 'update']);
    Route::delete('/revistas/{id}', [\App\Http\Controllers\Api\RevistaController::class, 'destroy']);

    Route::get('/revistas-cientificas', [\App\Http\Controllers\Api\RevistaCientificaController::class, 'index']);
    Route::post('/revistas-cientificas', [\App\Http\Controllers\Api\RevistaCientificaController::class, 'store']);
    Route::get('/revistas-cientificas/{id}', [\App\Http\Controllers\Api\RevistaCientificaController::class, 'show']);
    Route::put('/revistas-cientificas/{id}', [\App\Http\Controllers\Api\RevistaCientificaController::class, 'update']);
    Route::delete('/revistas-cientificas/{id}', [\App\Http\Controllers\Api\RevistaCientificaController::class, 'destroy']);

    Route::get('/programas-academicos', [\App\Http\Controllers\Api\ProgramaAcademicoController::class, 'index']);
    Route::post('/programas-academicos', [\App\Http\Controllers\Api\ProgramaAcademicoController::class, 'store']);
    Route::get('/programas-academicos/{id}', [\App\Http\Controllers\Api\ProgramaAcademicoController::class, 'show']);
    Route::put('/programas-academicos/{id}', [\App\Http\Controllers\Api\ProgramaAcademicoController::class, 'update']);
    Route::delete('/programas-academicos/{id}', [\App\Http\Controllers\Api\ProgramaAcademicoController::class, 'destroy']);

    Route::get('/materias-plan', [\App\Http\Controllers\Api\MateriaPlanController::class, 'index']);
    Route::post('/materias-plan', [\App\Http\Controllers\Api\MateriaPlanController::class, 'store']);
    Route::get('/materias-plan/{id}', [\App\Http\Controllers\Api\MateriaPlanController::class, 'show']);
    Route::put('/materias-plan/{id}', [\App\Http\Controllers\Api\MateriaPlanController::class, 'update']);
    Route::delete('/materias-plan/{id}', [\App\Http\Controllers\Api\MateriaPlanController::class, 'destroy']);

    Route::get('/requisitos-academicos', [\App\Http\Controllers\Api\RequisitoAcademicoController::class, 'index']);
    Route::post('/requisitos-academicos', [\App\Http\Controllers\Api\RequisitoAcademicoController::class, 'store']);
    Route::get('/requisitos-academicos/{id}', [\App\Http\Controllers\Api\RequisitoAcademicoController::class, 'show']);
    Route::put('/requisitos-academicos/{id}', [\App\Http\Controllers\Api\RequisitoAcademicoController::class, 'update']);
    Route::delete('/requisitos-academicos/{id}', [\App\Http\Controllers\Api\RequisitoAcademicoController::class, 'destroy']);

    Route::get('/tipos-postgrado', [\App\Http\Controllers\Api\TipoPostgradoController::class, 'index']);
    Route::post('/tipos-postgrado', [\App\Http\Controllers\Api\TipoPostgradoController::class, 'store']);
    Route::get('/tipos-postgrado/{id}', [\App\Http\Controllers\Api\TipoPostgradoController::class, 'show']);
    Route::put('/tipos-postgrado/{id}', [\App\Http\Controllers\Api\TipoPostgradoController::class, 'update']);
    Route::delete('/tipos-postgrado/{id}', [\App\Http\Controllers\Api\TipoPostgradoController::class, 'destroy']);

    Route::get('/fechas-pago', [\App\Http\Controllers\Api\FechaPagoController::class, 'index']);
    Route::post('/fechas-pago', [\App\Http\Controllers\Api\FechaPagoController::class, 'store']);
    Route::get('/fechas-pago/{id}', [\App\Http\Controllers\Api\FechaPagoController::class, 'show']);
    Route::put('/fechas-pago/{id}', [\App\Http\Controllers\Api\FechaPagoController::class, 'update']);
    Route::delete('/fechas-pago/{id}', [\App\Http\Controllers\Api\FechaPagoController::class, 'destroy']);

    Route::get('/fechas-doc', [\App\Http\Controllers\Api\FechaDocController::class, 'index']);
    Route::post('/fechas-doc', [\App\Http\Controllers\Api\FechaDocController::class, 'store']);
    Route::get('/fechas-doc/{id}', [\App\Http\Controllers\Api\FechaDocController::class, 'show']);
    Route::put('/fechas-doc/{id}', [\App\Http\Controllers\Api\FechaDocController::class, 'update']);
    Route::delete('/fechas-doc/{id}', [\App\Http\Controllers\Api\FechaDocController::class, 'destroy']);

    Route::get('/documentos-academicos', [\App\Http\Controllers\Api\DocumentoAcademicoController::class, 'index']);
    Route::post('/documentos-academicos', [\App\Http\Controllers\Api\DocumentoAcademicoController::class, 'store']);
    Route::get('/documentos-academicos/{id}', [\App\Http\Controllers\Api\DocumentoAcademicoController::class, 'show']);
    Route::put('/documentos-academicos/{id}', [\App\Http\Controllers\Api\DocumentoAcademicoController::class, 'update']);
    Route::delete('/documentos-academicos/{id}', [\App\Http\Controllers\Api\DocumentoAcademicoController::class, 'destroy']);

    Route::get('/ayudas', [\App\Http\Controllers\Api\AyudaController::class, 'index']);
    Route::post('/ayudas', [\App\Http\Controllers\Api\AyudaController::class, 'store']);
    Route::get('/ayudas/{id}', [\App\Http\Controllers\Api\AyudaController::class, 'show']);
    Route::put('/ayudas/{id}', [\App\Http\Controllers\Api\AyudaController::class, 'update']);
    Route::delete('/ayudas/{id}', [\App\Http\Controllers\Api\AyudaController::class, 'destroy']);

    Route::get('/cartas', [\App\Http\Controllers\Api\CartaController::class, 'index']);
    Route::post('/cartas', [\App\Http\Controllers\Api\CartaController::class, 'store']);
    Route::get('/cartas/{id}', [\App\Http\Controllers\Api\CartaController::class, 'show']);
    Route::put('/cartas/{id}', [\App\Http\Controllers\Api\CartaController::class, 'update']);
    Route::delete('/cartas/{id}', [\App\Http\Controllers\Api\CartaController::class, 'destroy']);

    Route::get('/cartas-modelo', [\App\Http\Controllers\Api\CartaModeloController::class, 'index']);
    Route::post('/cartas-modelo', [\App\Http\Controllers\Api\CartaModeloController::class, 'store']);
    Route::get('/cartas-modelo/{id}', [\App\Http\Controllers\Api\CartaModeloController::class, 'show']);
    Route::put('/cartas-modelo/{id}', [\App\Http\Controllers\Api\CartaModeloController::class, 'update']);
    Route::delete('/cartas-modelo/{id}', [\App\Http\Controllers\Api\CartaModeloController::class, 'destroy']);

    Route::get('/cartas-generadas', [\App\Http\Controllers\Api\CartaGenController::class, 'index']);
    Route::post('/cartas-generadas', [\App\Http\Controllers\Api\CartaGenController::class, 'store']);
    Route::get('/cartas-generadas/{id}', [\App\Http\Controllers\Api\CartaGenController::class, 'show']);
    Route::put('/cartas-generadas/{id}', [\App\Http\Controllers\Api\CartaGenController::class, 'update']);
    Route::delete('/cartas-generadas/{id}', [\App\Http\Controllers\Api\CartaGenController::class, 'destroy']);

    Route::get('/formularios-academicos', [\App\Http\Controllers\Api\FormularioAcademicoController::class, 'index']);
    Route::post('/formularios-academicos', [\App\Http\Controllers\Api\FormularioAcademicoController::class, 'store']);
    Route::get('/formularios-academicos/{id}', [\App\Http\Controllers\Api\FormularioAcademicoController::class, 'show']);
    Route::put('/formularios-academicos/{id}', [\App\Http\Controllers\Api\FormularioAcademicoController::class, 'update']);
    Route::delete('/formularios-academicos/{id}', [\App\Http\Controllers\Api\FormularioAcademicoController::class, 'destroy']);

    Route::get('/formularios-ins', [\App\Http\Controllers\Api\FormularioInsController::class, 'index']);
    Route::post('/formularios-ins', [\App\Http\Controllers\Api\FormularioInsController::class, 'store']);
    Route::get('/formularios-ins/{id}', [\App\Http\Controllers\Api\FormularioInsController::class, 'show']);
    Route::put('/formularios-ins/{id}', [\App\Http\Controllers\Api\FormularioInsController::class, 'update']);
    Route::delete('/formularios-ins/{id}', [\App\Http\Controllers\Api\FormularioInsController::class, 'destroy']);

    Route::get('/tests-academicos', [\App\Http\Controllers\Api\TestAcademicoController::class, 'index']);
    Route::post('/tests-academicos', [\App\Http\Controllers\Api\TestAcademicoController::class, 'store']);
    Route::get('/tests-academicos/{id}', [\App\Http\Controllers\Api\TestAcademicoController::class, 'show']);
    Route::put('/tests-academicos/{id}', [\App\Http\Controllers\Api\TestAcademicoController::class, 'update']);
    Route::delete('/tests-academicos/{id}', [\App\Http\Controllers\Api\TestAcademicoController::class, 'destroy']);

    Route::get('/grupos-academicos', [\App\Http\Controllers\Api\GrupoAcademicoController::class, 'index']);
    Route::post('/grupos-academicos', [\App\Http\Controllers\Api\GrupoAcademicoController::class, 'store']);
    Route::get('/grupos-academicos/{id}', [\App\Http\Controllers\Api\GrupoAcademicoController::class, 'show']);
    Route::put('/grupos-academicos/{id}', [\App\Http\Controllers\Api\GrupoAcademicoController::class, 'update']);
    Route::delete('/grupos-academicos/{id}', [\App\Http\Controllers\Api\GrupoAcademicoController::class, 'destroy']);

    Route::get('/historial', [\App\Http\Controllers\Api\HistorialController::class, 'index']);
    Route::post('/historial', [\App\Http\Controllers\Api\HistorialController::class, 'store']);
    Route::get('/historial/{id}', [\App\Http\Controllers\Api\HistorialController::class, 'show']);
    Route::put('/historial/{id}', [\App\Http\Controllers\Api\HistorialController::class, 'update']);
    Route::delete('/historial/{id}', [\App\Http\Controllers\Api\HistorialController::class, 'destroy']);

    Route::get('/hojas-evaluacion', [\App\Http\Controllers\Api\HojaEvaluacionController::class, 'index']);
    Route::post('/hojas-evaluacion', [\App\Http\Controllers\Api\HojaEvaluacionController::class, 'store']);
    Route::get('/hojas-evaluacion/{id}', [\App\Http\Controllers\Api\HojaEvaluacionController::class, 'show']);
    Route::put('/hojas-evaluacion/{id}', [\App\Http\Controllers\Api\HojaEvaluacionController::class, 'update']);
    Route::delete('/hojas-evaluacion/{id}', [\App\Http\Controllers\Api\HojaEvaluacionController::class, 'destroy']);

    Route::get('/moodles', [\App\Http\Controllers\Api\MoodleController::class, 'index']);
    Route::post('/moodles', [\App\Http\Controllers\Api\MoodleController::class, 'store']);
    Route::get('/moodles/{id}', [\App\Http\Controllers\Api\MoodleController::class, 'show']);
    Route::put('/moodles/{id}', [\App\Http\Controllers\Api\MoodleController::class, 'update']);
    Route::delete('/moodles/{id}', [\App\Http\Controllers\Api\MoodleController::class, 'destroy']);

    Route::get('/mdl-courses', [\App\Http\Controllers\Api\MdlCourseController::class, 'index']);
    Route::post('/mdl-courses', [\App\Http\Controllers\Api\MdlCourseController::class, 'store']);
    Route::get('/mdl-courses/{id}', [\App\Http\Controllers\Api\MdlCourseController::class, 'show']);
    Route::put('/mdl-courses/{id}', [\App\Http\Controllers\Api\MdlCourseController::class, 'update']);
    Route::delete('/mdl-courses/{id}', [\App\Http\Controllers\Api\MdlCourseController::class, 'destroy']);

    Route::get('/mdl-users', [\App\Http\Controllers\Api\MdlUserController::class, 'index']);
    Route::post('/mdl-users', [\App\Http\Controllers\Api\MdlUserController::class, 'store']);
    Route::get('/mdl-users/{id}', [\App\Http\Controllers\Api\MdlUserController::class, 'show']);
    Route::put('/mdl-users/{id}', [\App\Http\Controllers\Api\MdlUserController::class, 'update']);
    Route::delete('/mdl-users/{id}', [\App\Http\Controllers\Api\MdlUserController::class, 'destroy']);

    Route::get('/ingresos', [\App\Http\Controllers\Api\IngresoController::class, 'index']);
    Route::post('/ingresos', [\App\Http\Controllers\Api\IngresoController::class, 'store']);
    Route::get('/ingresos/{id}', [\App\Http\Controllers\Api\IngresoController::class, 'show']);
    Route::put('/ingresos/{id}', [\App\Http\Controllers\Api\IngresoController::class, 'update']);
    Route::delete('/ingresos/{id}', [\App\Http\Controllers\Api\IngresoController::class, 'destroy']);

    Route::get('/usuarios-moodle', [\App\Http\Controllers\Api\UsuarioMoodleController::class, 'index']);
    Route::post('/usuarios-moodle', [\App\Http\Controllers\Api\UsuarioMoodleController::class, 'store']);
    Route::get('/usuarios-moodle/{id}', [\App\Http\Controllers\Api\UsuarioMoodleController::class, 'show']);
    Route::put('/usuarios-moodle/{id}', [\App\Http\Controllers\Api\UsuarioMoodleController::class, 'update']);
    Route::delete('/usuarios-moodle/{id}', [\App\Http\Controllers\Api\UsuarioMoodleController::class, 'destroy']);

    Route::get('/usuarios-plan', [\App\Http\Controllers\Api\UsuarioPlanController::class, 'index']);
    Route::post('/usuarios-plan', [\App\Http\Controllers\Api\UsuarioPlanController::class, 'store']);
    Route::get('/usuarios-plan/{id}', [\App\Http\Controllers\Api\UsuarioPlanController::class, 'show']);
    Route::put('/usuarios-plan/{id}', [\App\Http\Controllers\Api\UsuarioPlanController::class, 'update']);
    Route::delete('/usuarios-plan/{id}', [\App\Http\Controllers\Api\UsuarioPlanController::class, 'destroy']);

    Route::get('/usuarios-plandoc', [\App\Http\Controllers\Api\UsuarioPlanDocController::class, 'index']);
    Route::post('/usuarios-plandoc', [\App\Http\Controllers\Api\UsuarioPlanDocController::class, 'store']);
    Route::get('/usuarios-plandoc/{id}', [\App\Http\Controllers\Api\UsuarioPlanDocController::class, 'show']);
    Route::put('/usuarios-plandoc/{id}', [\App\Http\Controllers\Api\UsuarioPlanDocController::class, 'update']);
    Route::delete('/usuarios-plandoc/{id}', [\App\Http\Controllers\Api\UsuarioPlanDocController::class, 'destroy']);

    Route::get('/usuarios-programa', [\App\Http\Controllers\Api\UsuarioProgramaController::class, 'index']);
    Route::post('/usuarios-programa', [\App\Http\Controllers\Api\UsuarioProgramaController::class, 'store']);
    Route::get('/usuarios-programa/{id}', [\App\Http\Controllers\Api\UsuarioProgramaController::class, 'show']);
    Route::put('/usuarios-programa/{id}', [\App\Http\Controllers\Api\UsuarioProgramaController::class, 'update']);
    Route::delete('/usuarios-programa/{id}', [\App\Http\Controllers\Api\UsuarioProgramaController::class, 'destroy']);

    Route::get('/usuarios-tipoprograma', [\App\Http\Controllers\Api\UsuarioTipoProgramaController::class, 'index']);
    Route::post('/usuarios-tipoprograma', [\App\Http\Controllers\Api\UsuarioTipoProgramaController::class, 'store']);
    Route::get('/usuarios-tipoprograma/{id}', [\App\Http\Controllers\Api\UsuarioTipoProgramaController::class, 'show']);
    Route::put('/usuarios-tipoprograma/{id}', [\App\Http\Controllers\Api\UsuarioTipoProgramaController::class, 'update']);
    Route::delete('/usuarios-tipoprograma/{id}', [\App\Http\Controllers\Api\UsuarioTipoProgramaController::class, 'destroy']);

    Route::get('/configuracion-academica', [\App\Http\Controllers\Api\ConfiguracionAcademicaController::class, 'index']);
    Route::post('/configuracion-academica', [\App\Http\Controllers\Api\ConfiguracionAcademicaController::class, 'store']);
    Route::get('/configuracion-academica/{id}', [\App\Http\Controllers\Api\ConfiguracionAcademicaController::class, 'show']);
    Route::put('/configuracion-academica/{id}', [\App\Http\Controllers\Api\ConfiguracionAcademicaController::class, 'update']);
    Route::delete('/configuracion-academica/{id}', [\App\Http\Controllers\Api\ConfiguracionAcademicaController::class, 'destroy']);

    Route::get('/archivos-academicos', [\App\Http\Controllers\Api\ArchivoAcademicoController::class, 'index']);
    Route::post('/archivos-academicos', [\App\Http\Controllers\Api\ArchivoAcademicoController::class, 'store']);
    Route::get('/archivos-academicos/{id}', [\App\Http\Controllers\Api\ArchivoAcademicoController::class, 'show']);
    Route::put('/archivos-academicos/{id}', [\App\Http\Controllers\Api\ArchivoAcademicoController::class, 'update']);
    Route::delete('/archivos-academicos/{id}', [\App\Http\Controllers\Api\ArchivoAcademicoController::class, 'destroy']);

    Route::get('/certificados-modelo', [\App\Http\Controllers\Api\CertificadoModeloController::class, 'index']);
    Route::post('/certificados-modelo', [\App\Http\Controllers\Api\CertificadoModeloController::class, 'store']);
    Route::get('/certificados-modelo/{id}', [\App\Http\Controllers\Api\CertificadoModeloController::class, 'show']);
    Route::put('/certificados-modelo/{id}', [\App\Http\Controllers\Api\CertificadoModeloController::class, 'update']);
    Route::delete('/certificados-modelo/{id}', [\App\Http\Controllers\Api\CertificadoModeloController::class, 'destroy']);

    Route::get('/paginas-academicas', [\App\Http\Controllers\Api\PaginaAcademicoController::class, 'index']);
    Route::post('/paginas-academicas', [\App\Http\Controllers\Api\PaginaAcademicoController::class, 'store']);
    Route::get('/paginas-academicas/{id}', [\App\Http\Controllers\Api\PaginaAcademicoController::class, 'show']);
    Route::put('/paginas-academicas/{id}', [\App\Http\Controllers\Api\PaginaAcademicoController::class, 'update']);
    Route::delete('/paginas-academicas/{id}', [\App\Http\Controllers\Api\PaginaAcademicoController::class, 'destroy']);

    Route::get('/bloques-ajustables', [\App\Http\Controllers\Api\BloqueAjustableController::class, 'index']);
    Route::post('/bloques-ajustables', [\App\Http\Controllers\Api\BloqueAjustableController::class, 'store']);
    Route::get('/bloques-ajustables/{id}', [\App\Http\Controllers\Api\BloqueAjustableController::class, 'show']);
    Route::put('/bloques-ajustables/{id}', [\App\Http\Controllers\Api\BloqueAjustableController::class, 'update']);
    Route::delete('/bloques-ajustables/{id}', [\App\Http\Controllers\Api\BloqueAjustableController::class, 'destroy']);

    Route::get('/bloques-plantilla', [\App\Http\Controllers\Api\BloquePlantillaController::class, 'index']);
    Route::post('/bloques-plantilla', [\App\Http\Controllers\Api\BloquePlantillaController::class, 'store']);
    Route::get('/bloques-plantilla/{id}', [\App\Http\Controllers\Api\BloquePlantillaController::class, 'show']);
    Route::put('/bloques-plantilla/{id}', [\App\Http\Controllers\Api\BloquePlantillaController::class, 'update']);
    Route::delete('/bloques-plantilla/{id}', [\App\Http\Controllers\Api\BloquePlantillaController::class, 'destroy']);

    Route::get('/secciones-bloque', [\App\Http\Controllers\Api\SeccionBloqueController::class, 'index']);
    Route::post('/secciones-bloque', [\App\Http\Controllers\Api\SeccionBloqueController::class, 'store']);
    Route::get('/secciones-bloque/{id}', [\App\Http\Controllers\Api\SeccionBloqueController::class, 'show']);
    Route::put('/secciones-bloque/{id}', [\App\Http\Controllers\Api\SeccionBloqueController::class, 'update']);
    Route::delete('/secciones-bloque/{id}', [\App\Http\Controllers\Api\SeccionBloqueController::class, 'destroy']);

    Route::get('/reg-componentes', [\App\Http\Controllers\Api\RegComponenteController::class, 'index']);
    Route::post('/reg-componentes', [\App\Http\Controllers\Api\RegComponenteController::class, 'store']);
    Route::get('/reg-componentes/{id}', [\App\Http\Controllers\Api\RegComponenteController::class, 'show']);
    Route::put('/reg-componentes/{id}', [\App\Http\Controllers\Api\RegComponenteController::class, 'update']);
    Route::delete('/reg-componentes/{id}', [\App\Http\Controllers\Api\RegComponenteController::class, 'destroy']);

    Route::get('/reg-forms', [\App\Http\Controllers\Api\RegFormController::class, 'index']);
    Route::post('/reg-forms', [\App\Http\Controllers\Api\RegFormController::class, 'store']);
    Route::get('/reg-forms/{id}', [\App\Http\Controllers\Api\RegFormController::class, 'show']);
    Route::put('/reg-forms/{id}', [\App\Http\Controllers\Api\RegFormController::class, 'update']);
    Route::delete('/reg-forms/{id}', [\App\Http\Controllers\Api\RegFormController::class, 'destroy']);

    Route::get('/funcionalidades-form', [\App\Http\Controllers\Api\FuncionalidadFormController::class, 'index']);
    Route::post('/funcionalidades-form', [\App\Http\Controllers\Api\FuncionalidadFormController::class, 'store']);
    Route::get('/funcionalidades-form/{id}', [\App\Http\Controllers\Api\FuncionalidadFormController::class, 'show']);
    Route::put('/funcionalidades-form/{id}', [\App\Http\Controllers\Api\FuncionalidadFormController::class, 'update']);
    Route::delete('/funcionalidades-form/{id}', [\App\Http\Controllers\Api\FuncionalidadFormController::class, 'destroy']);

    Route::get('/formatos-hoja-solicitud', [\App\Http\Controllers\Api\FormatoHojaSolicitudController::class, 'index']);
    Route::post('/formatos-hoja-solicitud', [\App\Http\Controllers\Api\FormatoHojaSolicitudController::class, 'store']);
    Route::get('/formatos-hoja-solicitud/{id}', [\App\Http\Controllers\Api\FormatoHojaSolicitudController::class, 'show']);
    Route::put('/formatos-hoja-solicitud/{id}', [\App\Http\Controllers\Api\FormatoHojaSolicitudController::class, 'update']);
    Route::delete('/formatos-hoja-solicitud/{id}', [\App\Http\Controllers\Api\FormatoHojaSolicitudController::class, 'destroy']);

    Route::get('/menus-academicos', [\App\Http\Controllers\Api\MenuAcademicoController::class, 'index']);
    Route::post('/menus-academicos', [\App\Http\Controllers\Api\MenuAcademicoController::class, 'store']);
    Route::get('/menus-academicos/{id}', [\App\Http\Controllers\Api\MenuAcademicoController::class, 'show']);
    Route::put('/menus-academicos/{id}', [\App\Http\Controllers\Api\MenuAcademicoController::class, 'update']);
    Route::delete('/menus-academicos/{id}', [\App\Http\Controllers\Api\MenuAcademicoController::class, 'destroy']);

    Route::get('/modulos-academicos', [\App\Http\Controllers\Api\ModuloAcademicoController::class, 'index']);
    Route::post('/modulos-academicos', [\App\Http\Controllers\Api\ModuloAcademicoController::class, 'store']);
    Route::get('/modulos-academicos/{id}', [\App\Http\Controllers\Api\ModuloAcademicoController::class, 'show']);
    Route::put('/modulos-academicos/{id}', [\App\Http\Controllers\Api\ModuloAcademicoController::class, 'update']);
    Route::delete('/modulos-academicos/{id}', [\App\Http\Controllers\Api\ModuloAcademicoController::class, 'destroy']);
});

Route::prefix('portal')->middleware(['portal.key', 'solo.activos', 'rate.portal:60,60', 'encrypt.portal'])->group(function () {

    Route::get('/banners', [BannerPortalController::class, 'index']);
    Route::get('/banners/{id}', [BannerPortalController::class, 'show']);

    Route::get('/eventos', [\App\Http\Controllers\Api\EventoController::class, 'index']);
    Route::get('/eventos/{id}', [\App\Http\Controllers\Api\EventoController::class, 'show']);

    Route::get('/redes-sociales', [RedSocialController::class, 'index']);

    Route::get('/configuracion', [\App\Http\Controllers\Api\ConfiguracionSitioController::class, 'publica']);

    Route::post('/mensajes-contacto', [MensajeContactoController::class, 'store']);

    Route::get('/preguntas-frecuentes', [\App\Http\Controllers\Api\PreguntaFrecuenteController::class, 'index']);

    Route::get('/galeria-categorias', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'index']);
    Route::get('/galeria-categorias/{id}', [\App\Http\Controllers\Api\GaleriaCategoriaController::class, 'show']);

    Route::get('/testimonios', [\App\Http\Controllers\Api\TestimonioController::class, 'index']);
    Route::get('/testimonios/{id}', [\App\Http\Controllers\Api\TestimonioController::class, 'show']);

    Route::get('/aliados', [\App\Http\Controllers\Api\AliadoController::class, 'index']);
    Route::get('/aliados/{id}', [\App\Http\Controllers\Api\AliadoController::class, 'show']);

    Route::get('/docentes-perfil', [\App\Http\Controllers\Api\DocentePerfilController::class, 'index']);
    Route::get('/docentes-perfil/{id}', [\App\Http\Controllers\Api\DocentePerfilController::class, 'show']);

    Route::get('/acreditaciones', [\App\Http\Controllers\Api\AcreditacionController::class, 'index']);
    Route::get('/acreditaciones/{id}', [\App\Http\Controllers\Api\AcreditacionController::class, 'show']);

    Route::get('/notas-prensa', [\App\Http\Controllers\Api\NotaPrensaController::class, 'index']);
    Route::get('/notas-prensa/{id}', [\App\Http\Controllers\Api\NotaPrensaController::class, 'show']);

    Route::get('/descargables', [\App\Http\Controllers\Api\DescargableController::class, 'index']);
    Route::get('/descargables/{id}', [\App\Http\Controllers\Api\DescargableController::class, 'show']);

    Route::get('/galeria-videos', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'index']);
    Route::get('/galeria-videos/{id}', [\App\Http\Controllers\Api\GaleriaVideoController::class, 'show']);

    Route::get('/calendario-academico', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'index']);
    Route::get('/calendario-academico/{id}', [\App\Http\Controllers\Api\CalendarioAcademicoController::class, 'show']);

    Route::get('/whatsapp-grupos', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'index']);
    Route::get('/whatsapp-grupos/{id}', [\App\Http\Controllers\Api\WhatsappGrupoController::class, 'show']);

    Route::get('/certificados/codigo/{codigo}', [\App\Http\Controllers\Api\CertificadoController::class, 'showByCode']);
    Route::post('/cert-verificaciones', [\App\Http\Controllers\Api\CertVerificacionController::class, 'store']);
});

Route::post('/public/preinscripciones', [\App\Http\Controllers\Api\PreinscripcionController::class, 'store'])
    ->middleware('throttle:30,1');
