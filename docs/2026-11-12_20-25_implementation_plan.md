# Plan de Implementación: Mi Postrecito (Postrecito de Jaan)

**Fecha:** 2026-11-12 20:25

Aplicación web de comercio electrónico y entregas a domicilio inspirada en la experiencia de **Mi Mandadito** (GEPP), adaptada a los productos y estética artesanal de **Postrecito de Jaan** (`https://postrecito.axiacorehub.com`), construida sobre **Laravel 13**, **FilamentPHP 5** y el SDK oficial **Laravel AI (`laravel/ai`)** con **Google Gemini** usando exclusivamente **Embeddings y Retrieval (RAG sin agentes)**.

---

## 1. Arquitectura y Stack Tecnológico

- **Framework Backend:** Laravel 13 (`^13.0`) con PHP 8.4.
- **Panel Administrativo y Gestión:** FilamentPHP v5 (`/admin`) para administrar Categorías/Sabores, Presentaciones, Productos del Catálogo, Conocimiento del Servicio (para RAG), Clientes y Pedidos.
- **Frontend de Tienda (Estilo Mi Mandadito):**
  - Interfaz reactiva con **Livewire** + **Tailwind CSS** + **Alpine.js**.
  - **Público (sin sesión):** Exploración completa del catálogo estilo *Mi Mandadito* (banners promocionales, filtros por sabor y presentación, buscador instantáneo, selector de zona/cobertura, tarjetas de producto con controles `+` / `-` y carrito lateral deslizable, sección de Nuestra Historia y Presentaciones).
  - **Autenticación requerida para pedidos:** Los visitantes pueden armar su carrito libremente; al proceder al checkout o confirmar un pedido (tanto en la tienda como en el Chatbot), el sistema solicita iniciar sesión o registrarse rápidamente (`/login` / `/register`) conservando su carrito e historial.
- **Chatbot con RAG (Sin Agentes) y Pedidos:**
  - Uso de `laravel/ai` (`Laravel\Ai\Embeddings`) conectado a **Google Gemini** (`GEMINI_API_KEY` en `.env`).
  - **Sin uso de clases `Agent` ni Tools de Agentes:**
    1. **Indexación (Embeddings):** Comando `php artisan rag:index` (y observer automático al guardar productos/documentos) que genera vectores de embeddings mediante `Embeddings::for([...])->generate()` de `laravel/ai` con el proveedor `gemini` (`gemini-embedding-001`) y los almacena en la tabla `knowledge_chunks` (con fallback determinista local si aún no se ha colocado la API Key en `.env`).
    2. **Retrieval (Recuperación por Similitud Coseno):** Al recibir un mensaje del usuario en el Chatbot, se genera el embedding de la consulta del usuario con `Embeddings::for([$query])->generate()`, se calcula la similitud coseno contra los embeddings del catálogo de productos y políticas de servicio de Postrecito de Jaan, y se recuperan los fragmentos más relevantes (`top-k`).
    3. **Generación aumentada y Creación de Pedidos:** Se construye el prompt con el contexto recuperado (sabores, presentaciones, precios, stock, cobertura, tiempos de entrega) y el estado actual del carrito/usuario, invocando directamente el endpoint de generación de Gemini con salida estructurada JSON para responder dudas del catálogo y **detectar intenciones de pedido** (agregar al carrito o confirmar pedido directo si el usuario está autenticado, o solicitar login si es invitado).

---

## 2. Modelo de Datos

- `flavors`: Sabores artesanales (Pay de Limón, Fresas con Crema, Carlota de Mango, Duraznos con Crema, Mousse de Oreo, Uvas con Crema, Tapioca, Congeladas de Rompope).
- `presentations`: Presentaciones (Vaso Individual 200 ml, Medio Litro 500 ml, Familiar 1 Litro, Formato Pay Extendida, Packs).
- `products`: Combinaciones comercializables de Sabor + Presentación con SKU, precio, stock, imagen/icono, promoción.
- `orders` y `order_items`: Pedidos de clientes autenticados con dirección, zona de cobertura, fecha y horario de entrega programada (estilo Mi Mandadito), método de pago, origen (`web` o `chatbot`) y estado.
- `knowledge_chunks`: Fragmentos vectorizados del catálogo de productos y políticas del servicio con columna `embedding` (JSON float array) para el motor RAG.

---

## 3. Componentes a Desarrollar

1. **Inicialización y Configuración (`Laravel 13` + `Filament 5` + `laravel/ai`):**
   - Configuración de `.env` y `.env.example` con `GEMINI_API_KEY`, `AI_DEFAULT_PROVIDER=gemini`, `GEMINI_EMBEDDING_MODEL=gemini-embedding-001`, y `GEMINI_CHAT_MODEL=gemini-2.5-flash`.
2. **Panel Administrativo FilamentPHP (`/admin`):**
   - `FlavorResource`, `PresentationResource`, `ProductResource`, `OrderResource`, `KnowledgeChunkResource` y `StatsOverviewWidget`.
3. **Tienda Frontend Estilo "Mi Mandadito" (`/`, `/mis-pedidos`, `/login`, `/register`):**
   - Catálogo público sin necesidad de login, selector de cobertura/entrega programada, carrito interactivo y requerimiento de autenticación al realizar el pedido.
4. **Servicio RAG (`RagChatbotService`) y Widget Flotante de Chatbot:**
   - Implementación estricta con **Embeddings + Retrieval** usando `Laravel\Ai\Embeddings` (sin agentes) y creación de pedidos desde el chatbot.
5. **Pruebas Automatizadas:**
   - Pruebas Feature verificando catálogo público, restricción de login para crear pedidos, panel Filament y flujo del RAG Chatbot.

