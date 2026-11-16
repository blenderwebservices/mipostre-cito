Actualizaré el documento de especificación para incluir un motor robusto de promociones, cupones y configuración de costos de envío. Además, agregaré un módulo específico para las "Campañas de Temporada" que le permitirá a Janet activar banners y descuentos para todas esas fechas importantes que mencionaste.
Aquí tienes el archivo actualizado con estas nuevas características integradas en la arquitectura y base de datos:




Postrecito de Jaan - App Specification
Exportar





Postrecito de Jaan - Especificación de Proyecto (Web App / PWA)
1. Visión General del Proyecto
Descripción: Aplicación web transaccional (SPA/PWA) que fusiona una landing page corporativa con una tienda en línea optimizada para móviles, inspirada en la UX/UI de "Mi Mandadito".
Objetivo: Permitir a los clientes de "Postrecito de Jaan" verificar cobertura, explorar el menú, realizar pedidos recurrentes, guardar múltiples direcciones, agendar entregas, y aprovechar promociones de temporada, todo desde una interfaz que simula una app nativa en el navegador.
2. Stack Tecnológico
Backend: Laravel 11+
Back-Office (Admin): FilamentPHP v3
Frontend (SPA/PWA): Laravel + Inertia.js (con Vue 3) o Laravel Livewire v3 (con navegación SPA wire:navigate). Recomendación para Antigravity: Utilizar el stack que mejor domine el desarrollador (Vue o Livewire).
Estilos: Tailwind CSS.
Base de Datos: MySQL / PostgreSQL.
3. Arquitectura de Interfaz (Frontend - Mobile First)
La aplicación debe sentirse como una app nativa, evitando recargas de página completas.
3.1. Layout Principal
Bottom Navigation Bar (Fija en móviles):
Inicio (Landing)
Menú/Tienda (Catálogo)
Promos (Cupones y Campañas Activas)
Mis Pedidos (Historial/Recompra)
Perfil (Direcciones, Ajustes)
Floating Action Button (FAB): Un carrito de compras flotante que muestra la cantidad de ítems y el subtotal, visible al navegar por el catálogo.
3.2. Pantallas Clave
Inicio (Landing - /):
Banner de Campaña Activa: Banners dinámicos para festividades (Buen Fin, Día de la Madre, Navidad, etc.).
Validador de Cobertura: Input de Código Postal o Colonia. Si hay cobertura, redirige al /menu. Si no, muestra mensaje amistoso.
Menú / Catálogo (/menu):
Carrusel horizontal superior de Categorías.
Grid de productos con selector rápido de Tamaño (ej. 200ml, 1 Litro) y botón "Agregar".
Promociones (/promos):
Listado de cupones activos que el usuario puede copiar.
Información de promociones de envío (ej. "Envío gratis en compras mayores a $200").
Checkout / Carrito (/cart):
Resumen del pedido.
Input para Código de Descuento (Cupón).
Cálculo dinámico de Envío basado en la zona.
Selector de Fecha y Bloque de Horario de entrega.
Selector de Dirección.
Método de pago (Efectivo/Transferencia).
Perfil de Usuario (/profile):
Libreta de Direcciones y Historial de Pedidos (One-Click Reorder).
4. Esquema de Base de Datos (Migraciones sugeridas)
Tablas Base (Catálogo y Usuarios)
users: phone_number (string).
categories: id, name, slug, description, image_path, is_active, sort_order.
products: id, category_id, name, slug, description, base_price, image_path, is_active.
product_variations: id, product_id, name, price, stock_quantity, is_active.
addresses: id, user_id, tag, street, exterior_number, interior_number, neighborhood, zip_code, references, latitude, longitude, is_default.
Tablas del Motor de Promociones y Envíos (NUEVO)
shipping_zones: id, zip_code (o nombre de colonia), delivery_cost, min_order_for_free_shipping, is_active.
coupons: id, code (ej. AMOR24), discount_type ('percentage', 'fixed'), discount_value, min_cart_value, valid_from, valid_until, usage_limit, times_used, is_active.
campaigns (Festividades): id, name (ej. 'Buen Fin', 'Día de la Candelaria'), banner_image_path, start_date, end_date, is_active, theme_color (opcional, para cambiar el color de la app).
Tablas de Transacción
orders: id, user_id, address_id, coupon_id (FK nullable), subtotal, discount_amount, shipping_cost, total_amount, delivery_date, delivery_time_slot, status (pending, preparing, en_route, delivered, cancelled), payment_method, notes.
order_items: id, order_id, product_variation_id, product_name_snapshot, variation_name_snapshot, quantity, unit_price, subtotal.
5. Panel de Administración (FilamentPHP Resources)
Recursos para el Back-Office de Janet/Administradores:
Dashboard: Ventas del día, Pedidos Pendientes, Productos más vendidos, Rendimiento de Campañas.
Catalog Management: CategoryResource y ProductResource (con RelationManager para product_variations).
Logistics Management:
OrderResource: Gestión de estados de pedido.
ShippingZoneResource: Definir qué códigos postales tienen cobertura y su costo exacto (o establecer envío gratis a partir de X monto).
Marketing Management (NUEVO):
CouponResource: Para crear códigos de descuento.
CampaignResource: Para configurar y programar las fechas especiales. (Semilla/Seeders sugeridos: Día del Amor, Día de la Madre, Buen Fin, Viernes Negro, Halloween, Día de la Independencia, Día de la Revolución, 5 de Mayo, Día de la Primavera, Navidad, Fin de Año, Día de la Candelaria).
User Management: UserResource.
6. Instrucciones Iniciales para Antigravity (Agente)
Inicializar proyecto Laravel e instalar dependencias (Filament, Inertia/Livewire, Tailwind).
Crear Modelos y Migraciones (Sección 4), incluyendo la lógica para aplicar Cupones y Costos de Envío dinámicos en el modelo Order.
Crear Seeders para poblar las campañas (Festividades mexicanas y comerciales) y zonas de envío de prueba.
Generar los Filament Resources (Sección 5) estructurados en grupos de navegación (Catálogo, Logística, Marketing).
Construir el Layout SPA y la pantalla de Inicio, asegurando que el banner de la Campaign activa se muestre dinámicamente según la fecha actual.


Editar directamente o con Gemini
Haz clic en cualquier parte para escribir y editar directamente, o selecciona texto para pedirle cambios a Gemini.
Con estos cambios, el agente entenderá que debe crear un módulo de "Marketing" en el panel de administración. Así Janet podrá entrar en cualquier momento, crear una campaña llamada "Día de la Candelaria", subir una foto de tamales o postres especiales, y configurar que automáticamente se active un cupón del 2 de febrero al 4 de febrero.
El archivo ya está actualizado en el editor. ¿Hay alguna otra función operativa que quieras incluir, como un sistema de notificaciones de WhatsApp cuando el pedido va en camino, o con esto ya lo ves listo para generar código?
