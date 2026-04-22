# Análisis de Mejoras del Chatbot CENEFCO

## Contexto

El chatbot de CENEFCO opera por WhatsApp usando una arquitectura híbrida:
- **Capa 1 — Keywords:** Detecta intenciones por palabras clave y despacha a handlers específicos
- **Capa 2 — IA (Ollama/qwen2.5):** Responde preguntas libres que no matchean ninguna keyword
- **Capa 3 — Menú fallback:** Si la IA no responde, muestra el menú principal

---

## Preguntas reales analizadas

| Mensaje del usuario | Problema detectado | Categoría |
|---|---|---|
| "Buenas tardes… Quiero información e lao cursos!!" | "tardes" no era keyword de saludo; typo "e lao" | Keywords + typos |
| "Son curso o diplomas??" | Pregunta comparativa de tipo de programa | IA — contexto insuficiente |
| "Que diplomados tienes??" | ✅ Ya manejado por keyword `diplomados` | OK |
| "Soy abogado que me recomiendas??" | Sin lógica de recomendación por perfil | IA — system prompt |
| "Y que precios manejan??" | `precio` no era keyword; contexto sin precios | Keywords + contexto |
| "Sabes algo sobre la educación a nivel mundial??" | Pregunta fuera de dominio | System prompt |
| "Necesito algo netamente de derecho!!!" | Búsqueda por área temática | IA — contexto + prompt |
| "De donde salió toda esa información??" | Pregunta sobre fuentes/credibilidad | System prompt |
| "Que aval universitario tienen??" | Sin info de acreditación en contexto | Keywords + contexto |
| "CENEFCO tiene resolución ministerial??" | Sin info institucional en contexto | Keywords + contexto |
| "Quien los autoriza???" | Sin info de autorización en contexto | Keywords + contexto |
| "Cuanto tiempo dura el programa de derecho deportivo?" | Sin campo duración en contexto | Contexto |
| "Temática que se desarrollara?" | Sin campo objetivo/temática en contexto | Contexto |
| "Precio de su programa de derecho deportivo" | Sin precio en contexto | Contexto |
| "hola me interesa el diplomado en derecho deportivo" | "me interesa" no era keyword de inscripción | Keywords |
| "quiero pagar para el diplomado" | Sin handler de pagos | Keywords + handler |
| "cuanto me cuesta?" | Sin precio en contexto | Contexto |
| "quisiera realizar el pago porfavor si me pasa QR" | Sin info de métodos de pago | Keywords + contexto |
| "no tengo tiempo para llamadas se puede pagar con qr directo" | Misma situación de pago | Keywords + contexto |
| Dos o tres preguntas en un mensaje | System prompt no instruía responder múltiples | System prompt |

---

## Cambios implementados

### 1. `config/bot.php` — Keywords expandidas

**Problema:** Keywords muy limitadas perdían mensajes válidos.

**Cambios:**
- `saludo` → agrega `tardes`, `noches`, `dias`, `menú`
- `programas` → agrega `que tienen`, `que ofrecen`, `que programas`, `educativos`, `cuentan`
- `inscripcion` → agrega `me interesa`, `interesado`, `interesada`, `quiero estudiar`, `quiero el`
- `pago` → **nuevo intent** con: `pago`, `precio`, `costo`, `cuanto cuesta`, `qr`, `transferencia`, `cuota`, `inversion`
- `acreditacion` → **nuevo intent** con: `aval`, `resolucion`, `ministerial`, `autoriza`, `validez`, `de donde`, `informacion`
- `soporte` → agrega `llamada`, `llamar`, `asesora`

### 2. `config/bot.php` — Nuevas secciones de configuración

**Problema:** Información de acreditación y pagos no existía en ningún lado.

**Nuevas secciones en `cenefco`:**

```php
'acreditacion' => [
    'aval'        => env('BOT_AVAL', ...),
    'resolucion'  => env('BOT_RESOLUCION', ...),
    'autoriza'    => env('BOT_AUTORIZA', ...),
    'descripcion' => env('BOT_ACREDITACION_DESC', ...),
],

'pagos' => [
    'metodos'       => env('BOT_METODOS_PAGO', ...),
    'instrucciones' => env('BOT_PAGO_INSTRUCCIONES', ...),
    'nota'          => env('BOT_PAGO_NOTA', ...),
],
```

**Variables `.env` a configurar:**
```env
BOT_AVAL="Universidad Mayor de San Simón (UMSS)"
BOT_RESOLUCION="Resolución Ministerial N° XXXX/XXXX"
BOT_AUTORIZA="Ministerio de Educación del Estado Plurinacional de Bolivia"
BOT_ACREDITACION_DESC="Todos nuestros programas cuentan con aval universitario..."
BOT_METODOS_PAGO="Transferencia bancaria, depósito, QR de pago"
BOT_PAGO_INSTRUCCIONES="Para el QR y detalles de pago, escribe soporte para que un asesor te contacte."
BOT_PAGO_NOTA="El pago puede realizarse en cuotas. Consulta con un asesor."
```

### 3. `AgentService::buildContext()` — Contexto enriquecido

**Problema:** El contexto enviado a la IA solo tenía nombre del programa y tipo. Faltaban datos críticos.

**Cambios:**
- `leftJoin` en lugar de `join` (evita perder programas sin `id_tipoprograma`)
- Aumentado a 15 programas (antes 10)
- **Nuevos campos por programa:** precio (`inversion`), duración (`creditaje`), a quién va dirigido (`dirigido`), fechas de inicio y fin, objetivo (primeros 150 chars)

**Ejemplo de contexto antes:**
```
- Diplomado en Derecho Deportivo [Diplomado], inscripciones desde 15/04/2026
```

**Ejemplo de contexto ahora:**
```
- Diplomado en Derecho Deportivo [Diplomado] | Precio: Bs. 2300 | Créditos/horas: 60
  | Dirigido a: Abogados, profesionales del deporte | Inicio: 15/04/2026 | Fin: 30/06/2026
  Objetivo: Desarrollar competencias en la normativa deportiva nacional e internacional...
```

### 4. `AgentService::buildSystemPrompt()` — Instrucciones mejoradas

**Nuevas reglas agregadas al system prompt:**

| Regla | Qué resuelve |
|---|---|
| Regla 7 — Múltiples preguntas | Responder TODAS cuando el usuario manda varias en un mensaje |
| Regla 8 — Recomendación por perfil | Usar campo `Dirigido a` para recomendar según profesión |
| Regla 9 — Precios | Responder con precio del contexto o derivar a asesor |
| Regla 10 — Acreditación | Usar sección de acreditación del contexto |
| Regla 11 — Pagos | Usar sección de métodos de pago del contexto |
| Regla 14 — Área sin programas | Indicar honestamente si no hay programas en ese campo |

### 5. `WhatsAppBotService` — Nuevos handlers directos

**Problema:** `pago` y `acreditacion` no tenían handlers, caían al menú o a la IA.

**Nuevos métodos:**
- `handlePago()` → Respuesta inmediata con métodos de pago, instrucciones y nota de cuotas
- `handleAcreditacion()` → Respuesta inmediata con aval, resolución y quién autoriza

---

## Flujo de una pregunta real ahora

**Usuario:** *"Soy abogado que me recomiendas?? Y que precios manejan??"*

```
WhatsAppBotService::routeByText()
  → keyword check: "precios" → intent "pago"
  → dispatchKeyword("pago") → handlePago() ← responde precios/QR
  
  PROBLEMA: Solo matchea el primer keyword.
  La segunda pregunta ("soy abogado") va al agente en el siguiente mensaje.
```

> ⚠️ **Limitación actual:** Si hay múltiples intents en un mensaje (ej: pago + recomendación), el sistema solo captura la primera keyword y despacha ese handler. La segunda pregunta se pierde hasta el próximo mensaje.

---

## Limitaciones pendientes (próximas mejoras)

### A. Detección de múltiples intents en un solo mensaje

**Situación:** *"Soy abogado que me recomiendas?? Y que precios manejan??"*

La arquitectura actual hace `return` en cuanto matchea la primera keyword. No procesa múltiples intents.

**Solución propuesta:**
```php
// En routeByText() — en lugar de return en el primer match:
$intents = $this->detectarIntents($text); // retorna array de intents

if (count($intents) === 1) {
    $this->dispatchKeyword($from, $intents[0]);
    return;
}

// Si hay múltiples intents → dejar que la IA los maneje todos
// (ya tiene el contexto completo para responder precio + recomendación)
$this->wa->sendText($from, '⏳ Un momento...');
$respuesta = $this->agent->responder($from, $text);
if ($respuesta) $this->wa->sendText($from, $respuesta);
```

**Complejidad:** Media. Requiere refactorizar `routeByText()`.

---

### B. Búsqueda de programas por área temática (filtro por texto)

**Situación:** *"Necesito diplomados en derecho"* / *"Algo para abogados"*

Actualmente la IA recibe todos los programas en el contexto y debe inferir cuáles aplican. Con 15 programas funciona. Con 50+ programas el contexto se vuelve demasiado largo.

**Solución propuesta:**
```php
// En AgentService::buildContext() — filtro dinámico opcional
public function responder(string $phone, string $input): ?string
{
    // Detectar área temática del input
    $area = $this->detectarAreaTematica($input); // "derecho", "salud", "educacion", etc.
    
    // Contexto normal + programas filtrados por esa área
    $contexto = Cache::remember("bot_ia_contexto_{$area}", 300, 
        fn() => $this->buildContext($area)
    );
}
```

**Complejidad:** Media-alta. Requiere lógica de detección de área y query con `LIKE` en `nombre_programa` y `dirigido`.

---

### C. Historial de conversación para contexto continuo

**Situación:** *"Y que precios manejan??"* (después de preguntar por un programa específico)

El pronombre "Y" implica que es continuación. La IA no tiene memoria del mensaje anterior.

**Solución propuesta:**
```php
// Pasar los últimos N mensajes de la conversación a la IA
$historial = $this->conv->getUltimosMensajes($from, limit: 5);
$messages = collect($historial)->map(fn($m) => 
    $m->tipo === 'entrante' 
        ? new UserMessage($m->contenido)
        : new AssistantMessage($m->contenido)
)->toArray();

Prism::text()
    ->withMessages([...$messages, new UserMessage($input)])
    ...
```

**Complejidad:** Media. Prism ya soporta arrays de mensajes. Requiere ajuste en `AgentService::responder()` y que `ConversationManager` exponga `getUltimosMensajes()`.

---

### D. Información de temáticas/módulos por programa

**Situación:** *"Temática que se desarrollara?"*

La tabla `t_programa` no tiene un campo de "módulos" o "contenido del programa". Solo `objetivo` y `nota`.

**Solución propuesta:**
- Opción A: Usar el campo `objetivo` completo (actualmente truncado a 150 chars) en respuestas específicas
- Opción B: Crear tabla `web_programa_modulo` con los módulos de cada programa
- Opción C: Agregar campo `contenido_programa` (TEXT) a `t_programa`

**Recomendación:** Opción A a corto plazo (cambiar el truncado en el contexto), Opción C a mediano plazo.

---

### E. Envío de QR real por WhatsApp

**Situación:** *"páseme QR directo"*, *"quiero pagar con QR"*

Actualmente se deriva a soporte humano para el QR.

**Solución propuesta:**
```php
// En handlePago() — si hay QR configurado, enviarlo directamente
if ($qrUrl = config('bot.cenefco.pagos.qr_image_url')) {
    $this->wa->sendImage($from, $qrUrl, 'Escanea este QR para realizar el pago.');
} else {
    // Derivar a soporte
}
```

**Complejidad:** Baja. Solo requiere tener la imagen del QR disponible como URL y configurarla en `.env`.

---

## Resumen de prioridades

| Prioridad | Mejora | Impacto | Esfuerzo |
|---|---|---|---|
| 🔴 Crítico | Variables `.env` de acreditación y pagos correctas | Alto | Bajo |
| 🔴 Crítico | Verificar `id_tipoprograma` en la BD (leftJoin) | Alto | Bajo |
| 🟠 Alta | Múltiples intents en un mensaje | Alto | Medio |
| 🟠 Alta | Historial de conversación en la IA | Alto | Medio |
| 🟡 Media | QR de pago directo por imagen | Medio | Bajo |
| 🟡 Media | Búsqueda de programas por área temática | Medio | Medio-Alto |
| 🟢 Baja | Tabla de módulos por programa | Alto | Alto |
