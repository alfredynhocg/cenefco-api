# Análisis de Migraciones — cenefco_api
## Objetivo: Página Web Institucional para Promoción de Cursos

> Fecha de análisis: 2026-04-15  
> Total de migraciones: 145 (73 tablas principales + 72 tablas de log)

---

## 1. Inventario de Tablas Existentes

### 1.1 Tablas de Sincronización Moodle (`mdl_*`)

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `mdl_course` | Cursos sincronizados desde Moodle | id, fullname, shortname, sigla, paralelo, cupo, fechas |
| `mdl_user` | Usuarios sincronizados desde Moodle | id, nombre_usuario, ci, email, telefono |

### 1.2 Sistema Académico (`t_*`)

#### Usuarios y Acceso

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_usuario` | Usuarios del sistema (estudiantes, docentes, admin) | id_us, nombre_usuario, password, appaterno, apmaterno, nombre, ci, email, celular, foto, id_niv, id_tipoprograma |
| `t_nivel` | Niveles de acceso del sistema | id_niv, titulo, codigo, validar_grupopermiso |
| `t_grupo` | Grupos de usuarios | id_grupo, siglagrupo, nombregrupo, espacio_laboral |
| `t_grupopermiso` | Grupos de permisos | id_grupopermiso |
| `t_permiso` | Permisos del sistema | id_permiso, id_grupopermiso, id_regform, xpermisos |
| `t_modulo` | Módulos del sistema/CMS | id_mod, titulo, posicion, tipo, acceso |
| `t_menu` | Menús del sistema | id_men, nombre_cat, sub_cat, acceso, icono, enlace |

#### Programas y Planes Académicos

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_programa` | **Programas académicos (cursos a promocionar)** | id_programa, nombre_programa, descripcion, foto, inicio/fin_actividades, inicio_inscripciones, dirigido, inversion, requisitos, objetivo, url_video, id_tipoprograma |
| `t_tipoprograma` | Tipos de programa (diplomado, maestría, etc.) | id_tipoprograma, nombre_tipoprograma |
| `t_plan` | Planes de estudio | id_plan, titulo, convenio, costo, nro_cuotas, descuento, url_campus, id_catplan, id_moodle, promocionar |
| `t_catplan` | Categorías de planes | id_catplan, titulocat |
| `t_carrera` | Carreras universitarias | id_carrera, nombre_carrera |
| `t_mension` | Menciones de programas | id_mension |
| `t_modalidadtitulacion` | Modalidades de titulación | id_modalidadtitulacion |

#### Oferta Académica y Docencia

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_imparte` | Asignación docente-materia (apertura de curso) | id_imp, periodo, gestion, id_us(docente), id_mat, paralelo, cupo, fechas_inicio/fin, id_moodle, firmas, certificados |
| `t_materia` | Materias/asignaturas | id_mat, sigla, nombremat, semestre, carga_horaria |
| `t_materia_plan` | Relación materia-plan | id_mat, id_plan |
| `t_inscripcion` | Inscripciones de estudiantes a cursos | id_ins, id_us, id_imp, fecha_ins, periodo, gestion |
| `t_nota` | Calificaciones | id_not, id_us, id_imp, id_mat, nota, periodo |
| `t_horario` | Horarios de clases | id_horar, id_imp, hora_inicio, hora_fin, periodos |
| `t_hora` | Catálogo de horas | id_hora |
| `t_requisito` | Prerequisitos de materias | id_req, id_mat, pre_id_mat |

#### Pagos y Finanzas

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_pago` | Pagos de estudiantes | id_pago, id_us, id_mat, monto_pagado, nro_boleta, fecha_deposito, nro_nit, observacion_pago |
| `t_tipopago` | Tipos de pago | id_tipopago |
| `t_tipopostgrado` | Config. de pago por tipo de postgrado | id_tipopost, id_plan, id_tipopago, descuento |
| `t_fechapago` | Fechas de pago programadas | id_fechapago |
| `t_mespago` | Meses de pago | id_mespago |
| `t_ingreso` | Registro de ingresos | id_ingreso |
| `t_ayuda` | Ayuda económica/becas | id_ayuda, id_us, monto_pagado, nro_recibo |

#### Documentos y Certificados

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_certificadomodelo` | Modelos/templates de certificados | id_certificadomodelo |
| `t_carta` | Cartas generadas | id_carta, id_us, id_plan, textocarta1/2/3 |
| `t_cartagen` | Generación de cartas | id_cartagen |
| `t_cartamodelo` | Modelos de cartas | id_cartamodelo |
| `t_documento` | Documentos del sistema | id_documento |
| `t_grupodocumento` | Documentos por grupo | id_grupodocumento |
| `t_plandoc` | Documentos requeridos por plan | id_plandoc |
| `t_usuarioplandoc` | Documentos entregados por usuario | id_usuarioplandoc |
| `t_fechadoc` | Fechas de documentos | id_fechadoc |
| `t_formato_hoja_solicitud` | Formato de hoja de solicitud | id_formato |
| `t_historial` | Historial académico | id_historial |
| `t_hojaevaluacion` | Hoja de evaluación | id_hojaevaluacion |

#### Investigación y Publicaciones

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_tesis` | Tesis publicadas | id_tesis, titulo_tesis, descripcion_tesis, fecha_publicacion, autor, tipo_tesis, archivo |
| `t_monografia` | Monografías | id_monografia, titulo, descripcion, archivo |
| `t_revistacientifica` | Revistas científicas | id_revistacientifica, titulo, descripcion, fecha_publicacion, archivo |
| `t_revista` | Revistas institucionales | id_revista, titulo_revista, descripcion, fecha_publicacion, archivo |

#### Moodle

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_moodle` | Configuración de servidores Moodle | id_moodle, titulo, cp_moodle_servidor, cp_url_campus |
| `t_usuariomoodle` | Relación usuario-cuenta Moodle | id_usmoodle, id_us, id_moodle, moodle_id_user |

#### Datos de Referencia

| Tabla | Propósito |
|-------|-----------|
| `t_ciudad` | Ciudades |
| `t_universidad` | Universidades (origen de estudiantes) |
| `t_tipouniversidad` | Tipos de universidad |
| `t_profesion` | Profesiones |
| `t_ocupacion` | Ocupaciones |

#### Relaciones Usuario-Programa

| Tabla | Propósito |
|-------|-----------|
| `t_usuarioplan` | Usuario asignado a un plan |
| `t_usuarioprograma` | Usuario asignado a un programa |
| `t_usuariotipoprograma` | Tipos de programa habilitados por usuario |

#### CMS Básico

| Tabla | Propósito | Campos clave |
|-------|-----------|-------------|
| `t_pagina` | Páginas del sitio | id_pagina, **titulo_pagina** (solo esto) |
| `t_bloqueajustable` | Bloques de contenido ajustables | id_bloqueajustable, id_pagina, id_bloqueplantilla, bd_tabla, texto_bloque |
| `t_bloqueplantilla` | Plantillas HTML de bloques | id_bloqueplantilla, titulo, cod_seccion, codigo_html |
| `t_seccionbloque` | Secciones dentro de un bloque | id_seccionbloque, id_bloqueajustable, texto_seccion |
| `t_articulo` | Artículos/noticias | id_art, titulo, autor, contenido, id_cat_art |
| `t_categoria_articulo` | Categorías de artículos | id_cat_art, nombre_cat, sub_cat |
| `t_boletin` | Boletines informativos | id_boletin, titulo_boletin, descripcion_boletin |
| `t_foto` | Galería de fotos | id_foto, titulo_foto, descripcion_foto, foto, fecha_foto |

#### Formularios y Sistema

| Tabla | Propósito |
|-------|-----------|
| `t_formulario` | Formularios de contacto/inscripción |
| `t_formularioins` | Formulario de inscripción |
| `t_funcionalidadform` | Funcionalidades de formularios |
| `t_regform` | Registro de formularios |
| `t_regcomponente` | Registro de componentes |
| `t_test` | Tests/evaluaciones con email |
| `t_configuracion` | Configuración global del sistema académico |
| `t_archivo` | Gestión de tipos de archivo permitidos |

---

## 2. Problemas Detectados en Tablas Existentes

### 2.1 Problema Central: Ausencia de Estructura Web Moderna

Ninguna tabla del sistema tiene los campos necesarios para una web institucional moderna. Comparando con el estándar de referencia dado:

```php
// Estructura de referencia (tabla noticias)
$table->string('slug', 300)->unique();           // ❌ NINGUNA tabla tiene slug
$table->string('entradilla', 500)->nullable();   // ❌ NINGUNA tabla tiene entradilla/excerpt
$table->string('imagen_principal_url')->nullable(); // ⚠️ Solo t_programa tiene 'foto' (nombre de archivo)
$table->string('imagen_alt')->nullable();         // ❌ NINGUNA tabla tiene alt text
$table->string('estado', 50)->default('borrador'); // ⚠️ Estado como tinyInteger(0/1), no como string semántico
$table->boolean('destacada')->default(false);    // ❌ NINGUNA tabla tiene campo destacado
$table->timestampTz('fecha_publicacion');        // ⚠️ Solo algunas tienen fecha, sin timezone
$table->integer('vistas')->default(0);           // ❌ NINGUNA tabla tiene contador de vistas
$table->string('meta_titulo')->nullable();       // ❌ NINGUNA tabla tiene campos SEO
$table->string('meta_descripcion')->nullable();  // ❌ NINGUNA tabla tiene meta descripción
$table->timestampTz('created_at')->useCurrent(); // ⚠️ Usan 'fecha_reg' (dateTime sin timezone)
$table->timestampTz('updated_at');               // ❌ No hay campo updated_at
$table->timestampTz('deleted_at');               // ❌ No hay soft deletes
// Foreign keys declarados                        // ❌ No hay FK constraints formales
```

### 2.2 Análisis por Tabla Crítica

#### `t_articulo` (Artículos/Noticias del Blog)
**Estado: Requiere reestructuración importante**

| Campo | Estado | Problema |
|-------|--------|---------|
| `slug` | ❌ Falta | Sin URLs amigables para SEO |
| `entradilla`/excerpt | ❌ Falta | Sin resumen para listados |
| `imagen_principal_url` | ❌ Falta | Solo hay `contenido` texto |
| `imagen_alt` | ❌ Falta | Accesibilidad y SEO |
| `destacada` | ❌ Falta | No se puede marcar artículo destacado |
| `fecha_publicacion` | ❌ Falta | Solo tiene `fecha_reg` (registro interno) |
| `vistas` | ❌ Falta | Sin métricas de popularidad |
| `meta_titulo` | ❌ Falta | Sin SEO on-page |
| `meta_descripcion` | ❌ Falta | Sin SEO on-page |
| `updated_at` | ❌ Falta | Sin tracking de modificaciones |
| `deleted_at` | ❌ Falta | Sin soft deletes |
| FK formal `id_cat_art` | ❌ Falta | Sin constraint de integridad |

#### `t_programa` (Programas a Promocionar — tabla más importante)
**Estado: Requiere campos web críticos**

| Campo | Estado | Problema |
|-------|--------|---------|
| `slug` | ❌ Falta | URL: `/programas/diplomado-en-finanzas` imposible |
| `meta_titulo` | ❌ Falta | SEO de cada programa |
| `meta_descripcion` | ❌ Falta | SEO de cada programa |
| `destacado` | ❌ Falta | No se pueden marcar programas en homepage |
| `vistas` | ❌ Falta | Sin saber qué programas interesan más |
| `imagen_banner` | ⚠️ Solo `foto` | Un solo campo para imagen de portada |
| `imagen_alt` | ❌ Falta | Accesibilidad |
| `orden` | ❌ Falta | Sin control de orden de aparición |
| `precio_display` | ⚠️ Está en `t_plan.costo` | Separado del programa en sí |
| `updated_at` | ❌ Falta | |
| `deleted_at` | ❌ Falta | |

#### `t_pagina` (Páginas del CMS)
**Estado: Extremadamente básica — solo tiene `titulo_pagina`**

| Campo | Estado | Problema |
|-------|--------|---------|
| `slug` | ❌ Falta | Sin URL amigable |
| `contenido_html` | ❌ Falta | No hay campo de contenido |
| `meta_titulo` | ❌ Falta | |
| `meta_descripcion` | ❌ Falta | |
| `imagen_og` | ❌ Falta | Open Graph para redes sociales |
| `template` | ❌ Falta | Sin selección de plantilla por página |

#### `t_foto` (Galería)
**Estado: Campos mínimos funcionales pero sin atributos web**

| Campo | Estado | Problema |
|-------|--------|---------|
| `alt` text | ❌ Falta | Accesibilidad y SEO de imágenes |
| `orden` | ❌ Falta | Sin orden de aparición |
| `id_categoria_galeria` | ❌ Falta | Sin categorización de galería |
| `destacada` | ❌ Falta | |

#### `t_boletin` (Boletines)
**Estado: Básico**

| Campo | Estado | Problema |
|-------|--------|---------|
| `slug` | ❌ Falta | |
| `imagen_url` | ❌ Falta | |
| `meta_titulo` | ❌ Falta | |
| `fecha_publicacion` | ❌ Falta | Solo `fecha_reg` |

#### `t_revista` y `t_revistacientifica`
**Estado: Aceptable para publicaciones, pero sin web features**

| Campo | Estado | Problema |
|-------|--------|---------|
| `slug` | ❌ Falta | |
| `imagen_portada` | ❌ Falta | Solo `archivo` (PDF) |
| `meta_titulo` | ❌ Falta | |
| `issn` | ❌ Falta | Para revistas científicas |

### 2.3 Patrón Común a Todas las Tablas
- **Primary key compuesta**: `['id_xxx', 'id_us_reg']` — es un patrón heredado del sistema antiguo, no compatible con Eloquent estándar de Laravel.
- **`id_us_reg`**: Registro de quién creó el registro, pero sin un campo separado de quién lo modificó por última vez.
- **`per_modificar`**: tinyInteger para controlar si se puede editar — sistema de permisos a nivel de fila no estándar.
- **`fecha_reg`**: dateTime sin timezone, en lugar de `timestampTz` o `timestamps()` de Laravel.
- **Estado**: `tinyInteger(0/1)` en lugar de `enum('borrador','publicado','archivado')` — no semántico.

---

## 3. Tablas Faltantes para Web Institucional de Promoción de Cursos

### 3.1 ALTA PRIORIDAD — Sin estas tablas la web no funciona

#### `web_banner` — Banners/Sliders del Hero
```php
Schema::create('web_banner', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('titulo', 300)->nullable();
    $table->string('subtitulo', 500)->nullable();
    $table->string('imagen_url', 255);
    $table->string('imagen_alt', 255)->nullable();
    $table->string('imagen_movil_url', 255)->nullable();
    $table->string('enlace_url', 255)->nullable();
    $table->string('enlace_texto', 100)->nullable();
    $table->string('enlace_target', 10)->default('_self'); // _self | _blank
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('fecha_inicio')->nullable();
    $table->timestampTz('fecha_fin')->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```
**Por qué falta**: Toda web institucional necesita un carrusel/hero de banners configurables desde el admin.

---

#### `web_configuracion_sitio` — Configuración Global del Sitio Web
```php
Schema::create('web_configuracion_sitio', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('clave', 100)->unique();   // logo_url, favicon_url, nombre_sitio, telefono, email_contacto, etc.
    $table->text('valor')->nullable();
    $table->string('tipo', 50)->default('text'); // text | image | boolean | json
    $table->string('grupo', 100)->nullable(); // general | seo | redes | contacto | colores
    $table->string('descripcion', 255)->nullable();
    $table->timestampTz('updated_at')->nullable();
});
```
**Por qué falta**: Logo, favicon, nombre del sitio, datos de contacto del footer, Google Analytics ID, etc. deben ser configurables sin deploy.

---

#### `web_suscriptor` — Suscriptores al Newsletter
```php
Schema::create('web_suscriptor', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('email', 100)->unique();
    $table->string('nombre', 200)->nullable();
    $table->boolean('activo')->default(true);
    $table->string('token_confirmacion', 100)->nullable();
    $table->boolean('confirmado')->default(false);
    $table->string('origen', 100)->nullable(); // homepage | popup | curso_detalle
    $table->timestampTz('fecha_confirmacion')->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
    $table->timestampTz('deleted_at')->nullable();
});
```
**Por qué falta**: Captación de leads es funcionalidad core de un sitio de promoción de cursos.

---

#### `web_contacto_mensaje` — Mensajes del Formulario de Contacto
```php
Schema::create('web_contacto_mensaje', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 200);
    $table->string('email', 100);
    $table->string('telefono', 20)->nullable();
    $table->string('asunto', 300)->nullable();
    $table->text('mensaje');
    $table->string('programa_interes', 200)->nullable(); // a qué programa se refiere
    $table->boolean('leido')->default(false);
    $table->timestampTz('fecha_lectura')->nullable();
    $table->string('ip_origen', 45)->nullable();
    $table->string('estado', 50)->default('nuevo'); // nuevo | leido | respondido | archivado
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```
**Por qué falta**: Los mensajes de contacto necesitan persistirse en BD, no solo enviarse por email.

---

### 3.2 ALTA PRIORIDAD — Mejoran significativamente la promoción

#### `web_testimonio` — Testimonios de Estudiantes
```php
Schema::create('web_testimonio', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 200);
    $table->string('cargo', 200)->nullable();       // Cargo o profesión
    $table->string('empresa', 200)->nullable();
    $table->text('testimonio');
    $table->tinyInteger('calificacion')->default(5); // 1-5 estrellas
    $table->string('foto_url', 255)->nullable();
    $table->string('foto_alt', 255)->nullable();
    $table->unsignedBigInteger('programa_id')->nullable(); // FK a t_programa
    $table->integer('orden')->default(0);
    $table->boolean('destacado')->default(false);
    $table->string('estado', 50)->default('publicado'); // borrador | publicado
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
    $table->timestampTz('deleted_at')->nullable();
});
```
**Por qué falta**: Los testimonios son uno de los principales factores de conversión en webs de cursos.

---

#### `web_faq` — Preguntas Frecuentes
```php
Schema::create('web_faq', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('pregunta', 500);
    $table->text('respuesta');
    $table->string('categoria', 100)->nullable(); // inscripcion | pagos | certificados | general
    $table->unsignedBigInteger('programa_id')->nullable(); // FAQ específica de un programa
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```
**Por qué falta**: Reduce carga de soporte y mejora UX. Google también indexa FAQs con rich snippets.

---

#### `web_aliado` — Aliados / Partners Institucionales
```php
Schema::create('web_aliado', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 200);
    $table->string('logo_url', 255);
    $table->string('logo_alt', 255)->nullable();
    $table->string('url_sitio', 255)->nullable();
    $table->string('descripcion', 500)->nullable();
    $table->string('tipo', 100)->nullable(); // aliado | convenio | acreditacion | patrocinador
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```
**Por qué falta**: Los logos de aliados/convenios generan confianza institucional.

---

### 3.3 MEDIA PRIORIDAD — Completan la experiencia web

#### `web_evento` — Eventos y Actividades
```php
Schema::create('web_evento', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('titulo', 300);
    $table->string('slug', 300)->unique();
    $table->string('entradilla', 500)->nullable();
    $table->text('descripcion')->nullable();
    $table->string('imagen_url', 255)->nullable();
    $table->string('imagen_alt', 255)->nullable();
    $table->string('tipo', 100)->nullable();    // webinar | conferencia | taller | ceremonia
    $table->string('modalidad', 50)->default('presencial'); // presencial | virtual | hibrido
    $table->string('lugar', 255)->nullable();
    $table->string('url_transmision', 255)->nullable();
    $table->string('url_registro', 255)->nullable();
    $table->boolean('gratuito')->default(true);
    $table->decimal('precio', 10, 2)->nullable();
    $table->integer('cupo_maximo')->nullable();
    $table->timestampTz('fecha_inicio');
    $table->timestampTz('fecha_fin')->nullable();
    $table->boolean('destacado')->default(false);
    $table->string('estado', 50)->default('publicado');
    $table->string('meta_titulo', 300)->nullable();
    $table->string('meta_descripcion', 500)->nullable();
    $table->integer('vistas')->default(0);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
    $table->timestampTz('deleted_at')->nullable();
});
```

---

#### `web_docente_perfil` — Perfil Público de Docentes/Expositores
```php
Schema::create('web_docente_perfil', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedInteger('usuario_id')->nullable(); // FK a t_usuario
    $table->string('nombre_completo', 300);
    $table->string('titulo_academico', 200)->nullable();
    $table->string('especialidad', 300)->nullable();
    $table->text('biografia')->nullable();
    $table->string('foto_url', 255)->nullable();
    $table->string('foto_alt', 255)->nullable();
    $table->string('linkedin_url', 255)->nullable();
    $table->string('email_publico', 100)->nullable();
    $table->integer('orden')->default(0);
    $table->boolean('mostrar_en_web')->default(true);
    $table->string('estado', 50)->default('publicado');
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
    $table->timestampTz('deleted_at')->nullable();
});
```

---

#### `web_popup` — Popups / Anuncios Emergentes
```php
Schema::create('web_popup', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('titulo', 300)->nullable();
    $table->text('contenido')->nullable();
    $table->string('imagen_url', 255)->nullable();
    $table->string('enlace_url', 255)->nullable();
    $table->string('enlace_texto', 100)->nullable();
    $table->string('posicion', 50)->default('center'); // center | bottom-left | bottom-right
    $table->integer('delay_segundos')->default(3);      // Retraso antes de mostrar
    $table->boolean('mostrar_una_vez')->default(true);  // Solo mostrar 1 vez por sesión
    $table->boolean('activo')->default(false);
    $table->timestampTz('fecha_inicio')->nullable();
    $table->timestampTz('fecha_fin')->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```

---

#### `web_etiqueta` — Tags/Etiquetas para Contenido
```php
Schema::create('web_etiqueta', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 100)->unique();
    $table->string('slug', 100)->unique();
    $table->string('color', 7)->nullable(); // Hex: #3B82F6
    $table->timestampTz('created_at')->nullable()->useCurrent();
});

Schema::create('web_articulo_etiqueta', function (Blueprint $table) {
    $table->unsignedBigInteger('articulo_id');
    $table->unsignedBigInteger('etiqueta_id');
    $table->primary(['articulo_id', 'etiqueta_id']);
});

Schema::create('web_programa_etiqueta', function (Blueprint $table) {
    $table->unsignedBigInteger('programa_id');
    $table->unsignedBigInteger('etiqueta_id');
    $table->primary(['programa_id', 'etiqueta_id']);
});
```

---

#### `web_categoria_programa` — Categorías Web de Programas (con imagen y slug)
```php
Schema::create('web_categoria_programa', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 200);
    $table->string('slug', 200)->unique();
    $table->text('descripcion')->nullable();
    $table->string('imagen_url', 255)->nullable();
    $table->string('imagen_alt', 255)->nullable();
    $table->string('icono', 100)->nullable(); // clase de icono (FontAwesome, etc.)
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->string('meta_titulo', 300)->nullable();
    $table->string('meta_descripcion', 500)->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```
> Nota: `t_catplan` existe pero es demasiado básica (solo tiene `titulocat`). Esta tabla la complementa para uso web.

---

### 3.4 BAJA PRIORIDAD — Nice to have

| Tabla | Propósito |
|-------|-----------|
| `web_redes_sociales` | Config. de redes sociales (Facebook, Instagram, etc.) con URL e icono |
| `web_galeria_categoria` | Categorías para organizar la galería de fotos |
| `web_notificacion_push` | Notificaciones web push (PWA) |
| `web_estadistica_acceso` | Log de vistas por página/programa para analytics interno |
| `web_descuento_promocion` | Cupones y promociones con fechas y porcentajes |

---

## 4. Campos a Agregar en Tablas Existentes

### `t_articulo` — Agregar campos web

```php
// Campos que faltan en t_articulo:
$table->string('slug', 300)->unique();
$table->string('entradilla', 500)->nullable();
$table->string('imagen_principal_url', 255)->nullable();
$table->string('imagen_alt', 255)->nullable();
$table->boolean('destacada')->default(false);
$table->timestampTz('fecha_publicacion')->nullable();
$table->integer('vistas')->default(0);
$table->string('meta_titulo', 300)->nullable();
$table->string('meta_descripcion', 500)->nullable();
$table->string('estado', 50)->default('borrador'); // cambiar de tinyInteger a string semántico
$table->timestampTz('updated_at')->nullable();
$table->timestampTz('deleted_at')->nullable();
```

### `t_programa` — Agregar campos web

```php
// Campos que faltan en t_programa:
$table->string('slug', 300)->unique();
$table->string('imagen_banner_url', 255)->nullable();
$table->string('imagen_alt', 255)->nullable();
$table->boolean('destacado')->default(false);
$table->integer('vistas')->default(0);
$table->integer('orden')->default(0);
$table->unsignedBigInteger('categoria_web_id')->nullable(); // FK a web_categoria_programa
$table->string('meta_titulo', 300)->nullable();
$table->string('meta_descripcion', 500)->nullable();
$table->string('estado', 50)->default('borrador'); // cambiar de tinyInteger
$table->timestampTz('fecha_publicacion')->nullable();
$table->timestampTz('updated_at')->nullable();
$table->timestampTz('deleted_at')->nullable();
```

### `t_pagina` — Agregar campos web (tabla casi vacía)

```php
// Campos que faltan en t_pagina:
$table->string('slug', 300)->unique();
$table->longText('contenido_html')->nullable();
$table->string('imagen_og_url', 255)->nullable(); // Open Graph
$table->string('template', 100)->nullable();      // Plantilla de diseño
$table->string('meta_titulo', 300)->nullable();
$table->string('meta_descripcion', 500)->nullable();
$table->boolean('indexar')->default(true);        // Para robots.txt / noindex
$table->integer('orden')->default(0);
$table->timestampTz('updated_at')->nullable();
$table->timestampTz('deleted_at')->nullable();
```

### `t_foto` — Agregar campos web

```php
// Campos que faltan en t_foto:
$table->string('alt', 255)->nullable();
$table->integer('orden')->default(0);
$table->boolean('destacada')->default(false);
$table->unsignedBigInteger('galeria_categoria_id')->nullable();
```

### `t_boletin` — Agregar campos web

```php
// Campos que faltan en t_boletin:
$table->string('slug', 300)->unique();
$table->string('imagen_url', 255)->nullable();
$table->timestampTz('fecha_publicacion')->nullable();
$table->string('meta_titulo', 300)->nullable();
$table->string('meta_descripcion', 500)->nullable();
$table->integer('vistas')->default(0);
```

---

## 4.6 Integración WhatsApp — Grupos para Inscritos

Esta funcionalidad permite que al inscribirse a un curso/taller, el estudiante reciba un enlace directo para unirse al grupo de WhatsApp correspondiente.

### Opción A: Campo directo en `t_imparte` (Recomendada)

`t_imparte` es la tabla que representa la apertura de un curso en un período específico (docente + materia + paralelo). Es el lugar correcto para el enlace de WhatsApp porque cada apertura puede tener su propio grupo.

```php
// Campos a agregar en t_imparte:
$table->string('whatsapp_grupo_url', 500)->nullable();        // Enlace de invitación al grupo
$table->string('whatsapp_grupo_nombre', 200)->nullable();     // Nombre del grupo (para mostrar al usuario)
$table->boolean('whatsapp_grupo_activo')->default(false);     // Habilitar/deshabilitar el enlace
$table->timestampTz('whatsapp_grupo_expira')->nullable();     // Fecha de expiración del enlace (WhatsApp los expira)
```

**Flujo completo:**

1. Admin crea/abre un curso en `t_imparte` → agrega el enlace de invitación de WhatsApp.
2. Estudiante se inscribe en `t_inscripcion`.
3. Al confirmar inscripción, el sistema muestra el enlace `whatsapp_grupo_url`.
4. Opcionalmente, se envía por email automáticamente al confirmar pago.

### Opción B: Tabla separada `web_whatsapp_grupo` (Para múltiples grupos por curso)

Si un mismo curso puede tener varios grupos (por ciudad, horario, etc.):

```php
Schema::create('web_whatsapp_grupo', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedInteger('imparte_id');               // FK a t_imparte
    $table->string('nombre', 200);                       // "Grupo WhatsApp - Turno Mañana"
    $table->string('enlace_invitacion', 500);            // https://chat.whatsapp.com/xxxxx
    $table->integer('capacidad_maxima')->nullable();     // WhatsApp permite hasta 1024 miembros
    $table->integer('miembros_actuales')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('fecha_expiracion_enlace')->nullable(); // Renovar cuando WhatsApp expire el link
    $table->string('descripcion', 300)->nullable();      // Instrucciones o nota para el estudiante
    $table->integer('orden')->default(0);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->foreign('imparte_id')->references('id_imp')->on('t_imparte')->onDelete('cascade');
    $table->index('imparte_id');
});
```

### Cuándo usar cada opción

| Escenario | Opción recomendada |
|-----------|-------------------|
| Un solo grupo por curso | **Opción A** (más simple, sin migración nueva) |
| Múltiples grupos por horario/ciudad | **Opción B** (más flexible) |
| Cursos con > 1024 inscritos | **Opción B** (varios grupos paralelos) |

### Campo adicional en `t_inscripcion`

```php
// Rastrear si el estudiante ya accedió al enlace:
$table->boolean('whatsapp_unido')->default(false);
$table->timestampTz('whatsapp_fecha_union')->nullable();
```

---

## 5. Resumen Ejecutivo

### Lo que YA tienes (bien cubierto)
- Sistema académico completo: inscripciones, notas, pagos, horarios
- Integración con Moodle
- Generación de certificados y cartas
- Gestión de usuarios con niveles y permisos
- CMS básico con páginas, bloques y plantillas HTML
- Artículos, fotos, boletines y revistas
- Publicaciones académicas (tesis, monografías, revistas científicas)

### Lo que te FALTA para la web institucional

| Prioridad | Qué falta | Impacto |
|-----------|-----------|---------|
| 🔴 Crítico | Slugs en `t_articulo`, `t_programa`, `t_pagina` | Sin URLs amigables = mal SEO |
| 🔴 Crítico | Campos SEO (meta_titulo, meta_descripcion) en contenido | Invisible para Google |
| 🔴 Crítico | Tabla `web_banner` | Sin hero/slider en homepage |
| 🔴 Crítico | Tabla `web_configuracion_sitio` | Logo, contactos, footer no configurables |
| 🔴 Crítico | Tabla `web_suscriptor` | Sin captación de leads |
| 🔴 Crítico | Tabla `web_contacto_mensaje` | Mensajes perdidos si falla el email |
| 🟠 Alta | Tabla `web_testimonio` | Baja conversión sin social proof |
| 🟠 Alta | Tabla `web_faq` | Más carga de soporte |
| 🟠 Alta | Campo `destacado` en `t_programa` y `t_articulo` | No hay control del homepage |
| 🟠 Alta | Campo `vistas` en contenido | Sin métricas de popularidad |
| 🟡 Media | Tabla `web_docente_perfil` | Sin presentación de docentes en web |
| 🟡 Media | Tabla `web_evento` | Sin agenda de eventos |
| 🟡 Media | Tabla `web_aliado` | Sin sección de aliados/convenios |
| 🟡 Media | Tags/etiquetas para artículos y programas | Sin filtros temáticos |
| 🟢 Baja | `web_popup`, `web_redes_sociales`, analytics interno | Nice to have |

### Número de migraciones nuevas estimadas

| Categoría | Migraciones nuevas |
|-----------|-------------------|
| Tablas nuevas `web_*` | ~12 tablas × 1 = **12** |
| Alteraciones de tablas existentes | ~6 tablas | 
| **Total** | **~18 migraciones nuevas** |

---

## 6. Orden Sugerido de Implementación

```
FASE 1 — SEO y Estructura básica web (Semana 1)
  ├── Alter t_articulo: slug, imagen, SEO, vistas, destacada, timestamps
  ├── Alter t_programa: slug, imagen_banner, SEO, vistas, destacado, timestamps
  ├── Alter t_pagina: slug, contenido_html, SEO
  └── Create web_configuracion_sitio

FASE 2 — Captación y Conversión (Semana 2)
  ├── Create web_banner
  ├── Create web_suscriptor
  ├── Create web_contacto_mensaje
  └── Create web_testimonio

FASE 3 — Enriquecimiento de Contenido (Semana 3)
  ├── Create web_faq
  ├── Create web_aliado
  ├── Create web_categoria_programa
  ├── Alter t_foto: alt, orden, destacada
  └── Create web_etiqueta + tablas pivote

FASE 4 — Funcionalidades adicionales (Semana 4)
  ├── Create web_docente_perfil
  ├── Create web_evento
  └── Create web_popup

FASE 5 — Credibilidad Institucional (Semana 5)
  ├── Create web_cifra_institucional
  ├── Create web_acreditacion
  ├── Create web_hito_institucional
  └── Create web_nota_prensa

FASE 6 — Conversión Avanzada (Semana 6)
  ├── Create web_preinscripcion
  ├── Create web_descargable
  ├── Create web_programa_resena
  └── Create web_galeria_video

FASE 7 — Operativa y SEO técnico (Semana 7)
  ├── Create web_calendario_academico
  ├── Create web_redes_sociales
  └── Create web_redireccion
```

---

## 7. Secciones y Tablas Adicionales Identificadas

Las siguientes tablas no estaban en el análisis original. Están agrupadas por la **sección del sitio web** a la que corresponden.

---

### SECCIÓN: "Sobre Nosotros / Institución"

Esta sección es la segunda más visitada en webs institucionales y actualmente no tiene soporte de BD adecuado.

---

#### `web_cifra_institucional` — Estadísticas / Contador de Logros

La sección de cifras ("500+ egresados", "20 años de experiencia", "98% de satisfacción") aparece en casi todas las webs de postgrado porque genera confianza de forma inmediata y visual.

```php
Schema::create('web_cifra_institucional', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('valor', 50);              // "500+", "20", "98%"
    $table->string('etiqueta', 200);          // "Egresados", "Años de experiencia"
    $table->string('icono', 100)->nullable(); // Clase de ícono (fa-users, fa-award, etc.)
    $table->string('color', 7)->nullable();   // Color hex: #3B82F6
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```

---

#### `web_hito_institucional` — Línea de Tiempo / Historia

Permite mostrar la historia de la institución como una línea de tiempo cronológica.

```php
Schema::create('web_hito_institucional', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('anio', 10);               // "2005", "2010-2012"
    $table->string('titulo', 300);
    $table->text('descripcion')->nullable();
    $table->string('imagen_url', 255)->nullable();
    $table->string('imagen_alt', 255)->nullable();
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```

---

#### `web_acreditacion` — Acreditaciones, Premios y Reconocimientos

Diferente a `web_aliado` (entidades externas con convenio). Esta tabla es para certificaciones, premios y sellos de calidad **obtenidos** por la institución.

```php
Schema::create('web_acreditacion', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 300);                  // "ISO 9001:2015", "Premio Excelencia Educativa"
    $table->string('entidad_otorgante', 200);        // "Bureau Veritas", "Ministerio de Educación"
    $table->string('tipo', 100)->nullable();         // acreditacion | certificacion | premio | sello
    $table->text('descripcion')->nullable();
    $table->string('logo_url', 255)->nullable();
    $table->string('logo_alt', 255)->nullable();
    $table->string('url_verificacion', 255)->nullable(); // Enlace para verificar la acreditación
    $table->date('fecha_obtencion')->nullable();
    $table->date('fecha_vencimiento')->nullable();   // Null = sin vencimiento
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```

---

#### `web_nota_prensa` — Apariciones en Medios de Comunicación

Sección "Como aparecimos en..." con logos de medios. Genera muchísima credibilidad.

```php
Schema::create('web_nota_prensa', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('titulo', 300);
    $table->string('medio', 200);                    // "El Deber", "Los Tiempos", etc.
    $table->string('logo_medio_url', 255)->nullable();
    $table->text('resumen')->nullable();
    $table->string('url_noticia', 500)->nullable();  // Enlace a la nota original
    $table->date('fecha_publicacion');
    $table->boolean('destacada')->default(false);
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```

---

### SECCIÓN: "Programas / Cursos" — Conversión Directa

---

#### `web_preinscripcion` — Pre-registro de Interés en un Programa

**La tabla de captación de leads más valiosa del sitio.** Permite que una persona se registre para ser notificada cuando un programa abra inscripciones, incluso si aún no está disponible. Mide la demanda real antes de lanzar un programa.

```php
Schema::create('web_preinscripcion', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedInteger('programa_id')->nullable();     // FK a t_programa
    $table->unsignedInteger('imparte_id')->nullable();      // FK a t_imparte (si ya está abierto)
    $table->string('nombre', 200);
    $table->string('email', 100);
    $table->string('telefono', 20)->nullable();
    $table->string('ciudad', 120)->nullable();
    $table->string('profesion', 200)->nullable();
    $table->text('mensaje')->nullable();                    // Consulta adicional
    $table->boolean('notificado')->default(false);          // Se le notificó que ya abrió
    $table->timestampTz('fecha_notificacion')->nullable();
    $table->string('estado', 50)->default('pendiente');     // pendiente | notificado | inscrito | descartado
    $table->string('origen', 100)->nullable();              // detalle_programa | popup | newsletter
    $table->string('utm_source', 100)->nullable();          // Trazabilidad de marketing
    $table->string('utm_medium', 100)->nullable();
    $table->string('utm_campaign', 100)->nullable();
    $table->string('ip_origen', 45)->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->index('email');
    $table->index('programa_id');
});
```

> **Flujo**: Visitante ve un programa "próximamente" → llena el formulario de pre-inscripción → queda en esta tabla → cuando el admin abre la convocatoria en `t_imparte`, el sistema marca `notificado = true` y dispara email automático.

---

#### `web_descargable` — Brochures y Materiales Descargables

Los brochures en PDF son el material de ventas más usado en postgrados. Esta tabla permite gestionarlos y rastrear cuántas veces se descarga cada uno (lead magnet).

```php
Schema::create('web_descargable', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 300);                      // "Brochure Diplomado en Finanzas 2026"
    $table->string('tipo', 100)->nullable();             // brochure | pensum | reglamento | guia
    $table->string('archivo_url', 255);                  // Ruta del PDF
    $table->string('imagen_portada_url', 255)->nullable(); // Miniatura del PDF
    $table->unsignedInteger('programa_id')->nullable();  // FK a t_programa (null = general)
    $table->boolean('requiere_datos')->default(true);    // Pide nombre+email antes de descargar
    $table->integer('descargas')->default(0);            // Contador de descargas
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
    $table->timestampTz('deleted_at')->nullable();

    $table->index('programa_id');
});

// Registro de cada descarga (para saber quién descargó qué)
Schema::create('web_descargable_registro', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('descargable_id');
    $table->string('nombre', 200)->nullable();
    $table->string('email', 100)->nullable();
    $table->string('telefono', 20)->nullable();
    $table->string('ip_origen', 45)->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();

    $table->foreign('descargable_id')->references('id')->on('web_descargable')->onDelete('cascade');
    $table->index('email');
});
```

---

#### `web_programa_resena` — Reseñas y Calificaciones de Egresados

Diferente a `web_testimonio` (curado por el admin). Las reseñas son enviadas por egresados y requieren moderación. Generan mayor credibilidad porque son más orgánicas y permiten mostrar un rating promedio por programa.

```php
Schema::create('web_programa_resena', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedInteger('programa_id');              // FK a t_programa
    $table->unsignedInteger('usuario_id')->nullable();   // FK a t_usuario (si está logueado)
    $table->string('nombre', 200);                       // Puede ser anónimo
    $table->string('cargo_actual', 200)->nullable();
    $table->tinyInteger('calificacion');                 // 1 a 5 estrellas
    $table->string('titulo_resena', 300)->nullable();
    $table->text('resena');
    $table->string('estado', 50)->default('pendiente');  // pendiente | aprobada | rechazada
    $table->boolean('verificado')->default(false);        // ¿Es un egresado verificado?
    $table->boolean('destacada')->default(false);
    $table->string('ip_origen', 45)->nullable();
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->index('programa_id');
    $table->index('estado');
});
```

---

### SECCIÓN: "Multimedia / Galería"

---

#### `web_galeria_video` — Galería de Videos Institucionales

`t_programa` solo tiene un campo `url_video`. Esta tabla cubre videos institucionales, webinars grabados, testimonios en video y presentaciones de docentes.

```php
Schema::create('web_galeria_video', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('titulo', 300);
    $table->text('descripcion')->nullable();
    $table->string('plataforma', 50)->default('youtube');    // youtube | vimeo | mp4
    $table->string('url_video', 500);                        // URL embebible o directa
    $table->string('video_id', 100)->nullable();             // ID del video en YouTube/Vimeo
    $table->string('miniatura_url', 255)->nullable();        // Thumbnail personalizado
    $table->string('duracion', 20)->nullable();              // "45:30"
    $table->string('tipo', 100)->nullable();                 // institucional | webinar | testimonio | docente
    $table->unsignedInteger('programa_id')->nullable();      // FK a t_programa (opcional)
    $table->boolean('destacado')->default(false);
    $table->integer('orden')->default(0);
    $table->integer('vistas')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
});
```

---

### SECCIÓN: "Configuración y SEO Técnico"

---

#### `web_redes_sociales` — Redes Sociales y Píxeles de Seguimiento

No es solo "poner el enlace de Facebook". Esta tabla también guarda los IDs de píxeles de seguimiento para marketing (Facebook Pixel, Google Ads, etc.).

```php
Schema::create('web_redes_sociales', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('red', 50);                    // facebook | instagram | youtube | linkedin | tiktok | x
    $table->string('nombre_display', 100)->nullable(); // "cenefco Oficial"
    $table->string('url', 255)->nullable();        // Enlace al perfil
    $table->string('icono_clase', 100)->nullable(); // fa-facebook, fa-instagram
    $table->string('pixel_id', 100)->nullable();   // Facebook Pixel ID, GA4 ID, etc.
    $table->boolean('mostrar_footer')->default(true);
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestampTz('updated_at')->nullable();
});
```

---

#### `web_redireccion` — Redirects 301 para SEO

Cuando se cambian slugs o se eliminan páginas, los redirects evitan errores 404 que dañan el SEO y la experiencia del usuario. Administrable desde el panel sin tocar código.

```php
Schema::create('web_redireccion', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('url_origen', 500);            // /programas/diplomado-antigua-url
    $table->string('url_destino', 500);           // /programas/nuevo-slug
    $table->smallInteger('codigo_http')->default(301); // 301 (permanente) | 302 (temporal)
    $table->integer('hits')->default(0);          // Cuántas veces se ejecutó el redirect
    $table->boolean('activo')->default(true);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->unique('url_origen');
    $table->index('activo');
});
```

---

#### `web_calendario_academico` — Fechas Importantes del Año

Diferente a `t_fechapago` (fechas de cobro internas). Esta tabla es para el calendario público del sitio: inicios de clases, inscripciones, graduaciones, feriados académicos.

```php
Schema::create('web_calendario_academico', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('titulo', 300);
    $table->text('descripcion')->nullable();
    $table->string('tipo', 100)->nullable();        // inscripcion | inicio_clases | examen | graduacion | feriado
    $table->string('color', 7)->nullable();         // Color para el calendario visual
    $table->unsignedInteger('programa_id')->nullable(); // NULL = aplica a todos
    $table->timestampTz('fecha_inicio');
    $table->timestampTz('fecha_fin')->nullable();   // NULL = evento de un solo día
    $table->boolean('todo_el_dia')->default(true);
    $table->boolean('destacado')->default(false);
    $table->boolean('publico')->default(true);      // False = solo visible en el panel admin
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->index('fecha_inicio');
    $table->index('programa_id');
});
```

---

## 8. Mapa Completo de Tablas `web_*` — Estado Final

| # | Tabla | Sección Web | Prioridad | Estado |
| --- | ------- | ------------ | --------- | ------ |
| 1 | `web_banner` | Hero / Slider | 🔴 Crítico | Planificada |
| 2 | `web_configuracion_sitio` | Global | 🔴 Crítico | Planificada |
| 3 | `web_suscriptor` | Newsletter | 🔴 Crítico | Planificada |
| 4 | `web_contacto_mensaje` | Contacto | 🔴 Crítico | Planificada |
| 5 | `web_testimonio` | Homepage / Programas | 🟠 Alta | Planificada |
| 6 | `web_faq` | Programas / General | 🟠 Alta | Planificada |
| 7 | `web_aliado` | Nosotros / Footer | 🟠 Alta | Planificada |
| 8 | `web_preinscripcion` | Detalle de Programa | 🟠 Alta | **Nueva** |
| 9 | `web_descargable` | Detalle de Programa | 🟠 Alta | **Nueva** |
| 10 | `web_descargable_registro` | Backend / Analytics | 🟠 Alta | **Nueva** |
| 11 | `web_cifra_institucional` | Nosotros / Homepage | 🟠 Alta | **Nueva** |
| 12 | `web_acreditacion` | Nosotros | 🟠 Alta | **Nueva** |
| 13 | `web_evento` | Eventos / Agenda | 🟡 Media | Planificada |
| 14 | `web_docente_perfil` | Programas / Nosotros | 🟡 Media | Planificada |
| 15 | `web_popup` | Global | 🟡 Media | Planificada |
| 16 | `web_etiqueta` | Blog / Programas | 🟡 Media | Planificada |
| 17 | `web_articulo_etiqueta` | Blog | 🟡 Media | Planificada |
| 18 | `web_programa_etiqueta` | Programas | 🟡 Media | Planificada |
| 19 | `web_categoria_programa` | Programas | 🟡 Media | Planificada |
| 20 | `web_programa_resena` | Detalle de Programa | 🟡 Media | **Nueva** |
| 21 | `web_galeria_video` | Galería / Programas | 🟡 Media | **Nueva** |
| 22 | `web_hito_institucional` | Nosotros | 🟡 Media | **Nueva** |
| 23 | `web_nota_prensa` | Nosotros / Prensa | 🟡 Media | **Nueva** |
| 24 | `web_redes_sociales` | Footer / Config | 🟡 Media | **Nueva** |
| 25 | `web_calendario_academico` | Agenda Pública | 🟡 Media | **Nueva** |
| 26 | `web_whatsapp_grupo` | Detalle Curso | 🟡 Media | Planificada |
| 27 | `web_redireccion` | SEO Técnico | 🟢 Baja | **Nueva** |
| 28 | `web_galeria_categoria` | Galería | 🟢 Baja | Planificada |
| 29 | `web_notificacion_push` | Global | 🟢 Baja | Planificada |
| 30 | `web_descuento_promocion` | Programas / Pagos | 🟢 Baja | Planificada |

---

## 9. Módulo de Lista de Aprobados y Generación Masiva de Certificados

Este es un módulo crítico compuesto por cuatro capas:

```text
MÓDULO CERTIFICADOS
├── t_cert_plantilla           → Plantilla JPG + configuración visual de campos
├── t_cert_plantilla_campo     → Posición X/Y, fuente y estilo de cada campo
├── t_lista_aprobados          → Lista oficial de aprobados por apertura de curso
└── t_certificado              → Certificado generado (código único + QR)
    └── t_cert_verificacion    → Log de cada verificación pública desde la web
```

### Flujo completo del módulo

```text
1. El admin termina un curso (t_imparte)
2. El sistema cruza t_nota con nota_min_aprobacion y muestra candidatos
3. El admin revisa y confirma → se crea t_lista_aprobados
4. El admin selecciona una plantilla JPG (t_cert_plantilla)
5. El sistema genera certificados en lote:
   a. Carga la imagen JPG de la plantilla
   b. Escribe el nombre y datos del estudiante en las posiciones configuradas
   c. Genera un código único (ej: cenefco-2026-A4X9K2)
   d. Genera el QR con la URL de verificación pública
   e. Embebe el QR en la imagen en la posición configurada
   f. Guarda el archivo JPG/PDF final
   g. Registra el certificado en t_certificado
6. Verificación pública: visitante ingresa código → el sistema devuelve si es VÁLIDO / ANULADO
```

---

### `t_cert_plantilla` — Plantillas de Certificado

Almacena cada plantilla JPG que se usará como base. Un mismo curso puede usar una plantilla de aprobación y otra de participación.

```php
Schema::create('t_cert_plantilla', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('nombre', 300);                        // "Certificado de Aprobación 2026"
    $table->string('tipo', 50)->default('aprobacion');    // aprobacion | participacion | asistencia
    $table->string('imagen_url', 500);                    // Ruta del JPG base (alta resolución)
    $table->integer('ancho_px')->default(3508);           // Ancho en píxeles (A4 a 300dpi)
    $table->integer('alto_px')->default(2480);            // Alto en píxeles
    $table->string('orientacion', 20)->default('horizontal'); // horizontal | vertical
    $table->string('formato_salida', 10)->default('jpg'); // jpg | pdf
    $table->integer('calidad_jpg')->default(95);          // 1-100, para jpg
    $table->string('fuente_default', 100)->default('Arial'); // Fuente por defecto
    $table->string('color_default', 7)->default('#000000');
    $table->string('estado', 50)->default('activo');
    $table->text('notas')->nullable();                    // Notas internas del admin
    $table->integer('id_us_reg')->default(0);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();
    $table->timestampTz('deleted_at')->nullable();
});
```

---

### `t_cert_plantilla_campo` — Campos Configurables por Plantilla

Cada campo define exactamente dónde y cómo se escribe un dato sobre la imagen JPG. El admin configura esto una vez por plantilla mediante una interfaz visual (drag & drop sobre la imagen).

```php
Schema::create('t_cert_plantilla_campo', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('plantilla_id');
    $table->string('clave', 100);              // nombre_completo | programa | fecha | codigo | qr_image
    $table->string('etiqueta', 200);           // "Nombre del Participante" (para el admin)
    $table->string('tipo', 50)->default('texto'); // texto | fecha | qr_image | imagen
    // Posición en la imagen (porcentaje para que sea responsive al tamaño)
    $table->decimal('pos_x_pct', 6, 3);       // % desde la izquierda (0.000 a 100.000)
    $table->decimal('pos_y_pct', 6, 3);       // % desde arriba
    $table->decimal('ancho_pct', 6, 3)->nullable();  // Ancho del área de texto (% del total)
    $table->decimal('alto_pct', 6, 3)->nullable();   // Alto del área (para QR)
    // Estilo tipográfico
    $table->string('fuente', 100)->nullable(); // Hereda de plantilla si null
    $table->integer('tamano_pt')->default(36); // Tamaño en puntos
    $table->string('color', 7)->default('#000000');
    $table->string('alineacion', 20)->default('center'); // left | center | right
    $table->boolean('negrita')->default(false);
    $table->boolean('cursiva')->default(false);
    $table->string('mayusculas', 20)->default('none');  // none | upper | lower | title
    $table->string('valor_fijo', 300)->nullable();      // Para campos con texto estático
    $table->boolean('activo')->default(true);
    $table->integer('orden')->default(0);

    $table->foreign('plantilla_id')->references('id')->on('t_cert_plantilla')->onDelete('cascade');
    $table->index('plantilla_id');
});
```

**Claves predefinidas del sistema (`clave`):**

| Clave | Dato que se inserta |
| --- | --- |
| `nombre_completo` | Nombre + apellidos del egresado |
| `nombre_titulo` | Título académico + nombre (ej: "Lic. Juan Pérez") |
| `programa` | Nombre del programa/curso |
| `fecha_emision` | Fecha de emisión del certificado |
| `fecha_inicio` | Fecha inicio del curso |
| `fecha_fin` | Fecha fin del curso |
| `duracion_horas` | Horas académicas del curso |
| `nota` | Nota final obtenida |
| `codigo_verificacion` | Código único (ej: cenefco-2026-A4X9K2) |
| `qr_image` | Imagen QR generada apuntando a la URL de verificación |
| `valor_fijo` | Texto estático (firma, cargo del director, etc.) |

---

### `t_lista_aprobados` — Lista Oficial de Aprobados

Registro formal de quién aprobó un curso. Es la fuente de verdad para generar certificados. Separado de `t_nota` para permitir al admin hacer ajustes manuales (casos especiales, correcciones) sin alterar el historial de notas.

```php
Schema::create('t_lista_aprobados', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedInteger('imparte_id');             // FK a t_imparte (apertura del curso)
    $table->unsignedInteger('usuario_id');             // FK a t_usuario (estudiante)
    $table->unsignedInteger('inscripcion_id')->nullable(); // FK a t_inscripcion
    // Datos del resultado
    $table->decimal('nota_final', 5, 2)->nullable();   // Nota final obtenida
    $table->decimal('nota_minima', 5, 2)->nullable();  // Nota mínima exigida al momento
    $table->string('condicion', 50)->default('aprobado'); // aprobado | participacion | reprobado
    $table->string('observacion', 500)->nullable();    // Nota del admin si ajustó manualmente
    $table->boolean('ajuste_manual')->default(false);  // True si el admin modificó la condición
    // Estado del certificado
    $table->string('estado_certificado', 50)->default('pendiente');
    // pendiente | generado | enviado | descargado
    $table->boolean('notificado_email')->default(false);
    $table->timestampTz('fecha_notificacion')->nullable();
    // Control
    $table->unsignedInteger('registrado_por');         // FK a t_usuario (admin que cerró la lista)
    $table->integer('id_us_reg')->default(0);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->unique(['imparte_id', 'usuario_id']);      // Un estudiante una vez por apertura
    $table->index('imparte_id');
    $table->index('condicion');
});
```

---

### `t_certificado` — Certificado Generado

El registro central del certificado una vez generado. Contiene el código único que permite la verificación pública.

```php
Schema::create('t_certificado', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('lista_aprobado_id');   // FK a t_lista_aprobados
    $table->unsignedBigInteger('plantilla_id');        // FK a t_cert_plantilla (qué plantilla se usó)
    $table->unsignedInteger('usuario_id');             // FK a t_usuario (desnormalizado para consultas)
    $table->unsignedInteger('imparte_id');             // FK a t_imparte (desnormalizado)
    // Datos impresos en el certificado (snapshot al momento de generación)
    $table->string('nombre_en_certificado', 300);      // Nombre exacto que aparece impreso
    $table->string('programa_en_certificado', 300);    // Nombre exacto del programa impreso
    $table->string('condicion', 50);                   // aprobado | participacion
    $table->decimal('nota_final', 5, 2)->nullable();
    $table->integer('horas_academicas')->nullable();
    $table->date('fecha_inicio_curso')->nullable();
    $table->date('fecha_fin_curso')->nullable();
    // Identificación única
    $table->string('codigo_verificacion', 50)->unique(); // cenefco-2026-A4X9K2
    $table->string('qr_url', 500)->nullable();           // URL completa que codifica el QR
    // Archivos generados
    $table->string('archivo_url', 500)->nullable();      // Ruta del JPG/PDF final generado
    $table->string('archivo_miniatura_url', 255)->nullable(); // Miniatura para preview
    // Estado
    $table->string('estado', 50)->default('generado');
    // generado | enviado | descargado | anulado
    $table->text('motivo_anulacion')->nullable();
    $table->unsignedInteger('anulado_por')->nullable(); // FK a t_usuario (admin)
    $table->timestampTz('fecha_anulacion')->nullable();
    // Estadísticas
    $table->integer('veces_verificado')->default(0);
    $table->integer('veces_descargado')->default(0);
    $table->timestampTz('ultima_verificacion')->nullable();
    // Control
    $table->integer('id_us_reg')->default(0);
    $table->timestampTz('created_at')->nullable()->useCurrent();
    $table->timestampTz('updated_at')->nullable();

    $table->index('codigo_verificacion');
    $table->index('usuario_id');
    $table->index('imparte_id');
    $table->index('estado');
});
```

**Formato del `codigo_verificacion`:**

```php
// Formato: cenefco-{AÑO}-{6 caracteres alfanuméricos en mayúsculas}
// Ejemplo:  cenefco-2026-A4X9K2
$codigo = 'cenefco-' . date('Y') . '-' . strtoupper(Str::random(6));
// Validar que sea único antes de guardar
```

---

### `t_cert_verificacion` — Log de Verificaciones Públicas

Registra cada vez que alguien verifica un certificado desde la web pública. Útil para detectar verificaciones masivas sospechosas y como auditoría.

```php
Schema::create('t_cert_verificacion', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('certificado_id')->nullable(); // Null si el código no existe
    $table->string('codigo_consultado', 100);     // Código ingresado por el usuario
    $table->string('resultado', 20);              // valido | anulado | no_encontrado
    $table->string('ip_origen', 45)->nullable();
    $table->string('user_agent', 500)->nullable();
    $table->string('pais', 100)->nullable();      // Geolocalización opcional
    $table->timestampTz('created_at')->nullable()->useCurrent();

    $table->index('certificado_id');
    $table->index('ip_origen');
    $table->index('created_at');
});
```

---

### Lógica de Generación Masiva (Pseudocódigo del proceso)

```php
// CertificadoService::generarLote(int $imparteId, int $plantillaId): void
//
// 1. Obtener lista de aprobados del curso
$aprobados = ListaAprobados::where('imparte_id', $imparteId)
    ->where('condicion', '!=', 'reprobado')
    ->where('estado_certificado', 'pendiente')
    ->with('usuario')
    ->get();
//
// 2. Cargar la imagen plantilla una sola vez
$plantilla    = CertPlantilla::with('campos')->find($plantillaId);
$imagenBase   = imagecreatefromjpeg(storage_path($plantilla->imagen_url));
//
// 3. Por cada aprobado, clonar la imagen y escribir los datos
foreach ($aprobados as $aprobado) {
    $imagen = imagecopy($imagenBase); // Clonar
//
    foreach ($plantilla->campos as $campo) {
        switch ($campo->clave) {
            case 'nombre_completo':
                escribirTexto($imagen, $campo, $aprobado->usuario->nombre_completo);
                break;
            case 'codigo_verificacion':
                $codigo = generarCodigoUnico(); // cenefco-2026-XXXXXX
                escribirTexto($imagen, $campo, $codigo);
                break;
            case 'qr_image':
                $qrUrl = url('/verificar/' . $codigo);
                $qr    = generarQR($qrUrl); // Librería: SimpleSoftwareIO/simple-qrcode
                pegarImagen($imagen, $campo, $qr);
                break;
        }
    }
//
    // 4. Guardar archivo
    $rutaArchivo = "certificados/{$imparteId}/{$codigo}.jpg";
    imagejpeg($imagen, storage_path($rutaArchivo), 95);
//
    // 5. Registrar en BD
    Certificado::create([
        'lista_aprobado_id'    => $aprobado->id,
        'plantilla_id'         => $plantillaId,
        'usuario_id'           => $aprobado->usuario_id,
        'imparte_id'           => $imparteId,
        'nombre_en_certificado'=> $aprobado->usuario->nombre_completo,
        'codigo_verificacion'  => $codigo,
        'qr_url'               => url('/verificar/' . $codigo),
        'archivo_url'          => $rutaArchivo,
        'estado'               => 'generado',
    ]);
//
    $aprobado->update(['estado_certificado' => 'generado']);
}
```

---

### Endpoint Público de Verificación

```php
// Ruta pública: GET /verificar/{codigo}
// También puede aceptar carnet: GET /verificar?carnet=12345678
//
// CertificadoController::verificar(string $codigo)

$certificado = Certificado::where('codigo_verificacion', $codigo)
    ->with(['usuario', 'imparte.materia'])
    ->first();

// Registrar el intento de verificación
CertVerificacion::create([
    'certificado_id'   => $certificado?->id,
    'codigo_consultado'=> $codigo,
    'resultado'        => match(true) {
        is_null($certificado)             => 'no_encontrado',
        $certificado->estado === 'anulado'=> 'anulado',
        default                           => 'valido',
    },
    'ip_origen'        => request()->ip(),
]);

// Incrementar contador
$certificado?->increment('veces_verificado');
$certificado?->update(['ultima_verificacion' => now()]);

// Retornar vista pública con resultado
return view('verificacion.resultado', compact('certificado'));
```

**Vista pública mostraría:**

- ✅ **CERTIFICADO VÁLIDO** (verde) — con nombre, programa, fecha y nota
- ❌ **CERTIFICADO ANULADO** (rojo) — con motivo de anulación
- ⚠️ **CÓDIGO NO ENCONTRADO** (amarillo) — con formulario para reintentar

---

### Resumen del Módulo

| Tabla | Propósito | Registros típicos |
| --- | --- | --- |
| `t_cert_plantilla` | Plantillas JPG configuradas | 5-20 |
| `t_cert_plantilla_campo` | Campos por plantilla | 5-15 por plantilla |
| `t_lista_aprobados` | Aprobados por curso | 1 por estudiante por curso |
| `t_certificado` | Certificados emitidos | 1 por aprobado |
| `t_cert_verificacion` | Log de verificaciones | N verificaciones por certificado |

### Dependencias de paquetes PHP sugeridos

```bash
# Generación de QR
composer require simplesoftwareio/simple-qrcode

# Manipulación de imágenes (escribir texto sobre JPG)
composer require intervention/image

# Generación de PDF (alternativa al JPG)
composer require barryvdh/laravel-dompdf
```
