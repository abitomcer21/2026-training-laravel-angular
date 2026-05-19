# 2026 Training: TPV (Terminal Punto de Venta)

> **Proyecto:** Sistema TPV para hostelería con Laravel + Angular + Ionic  
> **Autor:** Abigail Tomás  
> **Periodo de prácticas:** Marzo - Junio 2026  
> **Actualizado a fecha de :** 19 de Mayo 2026  

Este proyecto consiste en un **Terminal Punto de Venta (TPV)** diseñado específicamente para el sector de la hostelería. Permite gestionar la operativa diaria de un negocio: control de mesas y zonas, gestión de productos y familias, registro de ventas, y generación de tickets.

El sistema está compuesto por dos partes diferenciadas:
- **Panel de gestión**: Área de administración para gestionar la configuración del negocio (productos, impuestos, zonas, mesas, usuarios)
- **Front de venta (TPV)**: Interfaz táctil para el uso diario del personal, donde se gestionan aperturas de mesa, pedidos y cierres de venta
El diseño está enfocado a que sea fácil de utilizar por cualquier usuario, priorizando la intuición y la rapidez en el día a día del local.

Este proyecto es el trabajo desarrollado durante el periodo de prácticas, aplicando enfoques como DDD (Domain-Driven Design), arquitectura hexagonal y, actualmente, CQRS.

## Índice

- [Estado del proyecto](#estado-del-proyecto)
  - [Trabajo realizado por hito](#trabajo-realizado-por-hito)
  - [Mejoras implementadas](#mejoras-implementadas-hito-6---en-proceso)
- [Prerrequisitos](#prerrequisitos)
- [Cómo empezar](#cómo-empezar)
- [Estructura del proyecto](#estructura-del-proyecto)
  - [Backend (`backend/`)](#backend-backend)
  - [Frontend (`frontend/`)](#frontend-frontend)
  - [DbGate (cliente de base de datos)](#dbgate-cliente-de-base-de-datos)
- [Objetivos de aprendizaje](#objetivos-de-aprendizaje)
- [Buenas prácticas](#buenas-prácticas)
- [Estilo de código](#estilo-de-código)

---


## Estado del proyecto - Hitos completados

| Hito | Estado | Descripción |
|------|--------|-------------|
| Hito 1 | COMPLETADO | Modelo de datos (migraciones, seeders, relaciones) |
| Hito 2 | COMPLETADO | API REST: Backoffice (CRUD, autenticación) |
| Hito 3 | COMPLETADO | Interfaz Backoffice (gestión de entidades) |
| Hito 4 | COMPLETADO | Front de venta TPV (mesas, productos, ventas) |
| Hito 5 | PENDIENTE | Informes (opcional) |
| Hito 6 | EN PROCESO | Mejoras funcionales (5 implementadas + CQRS en curso) |

### Trabajo realizado por hito

**Hito 1 - Modelo de datos** (COMPLETADO)
- Migraciones para todas las entidades (familias, impuestos, productos, zonas, mesas, ventas, lineas)
- Seeders con datos de prueba
- Soft deletes implementados

**Hito 2 - API REST: Backoffice** (COMPLETADO)
- Autenticacion de usuarios (sesion/token)
- CRUD completo para familias, impuestos, productos, zonas, mesas
- Activacion/desactivacion de productos y familias
- Validaciones y respuestas de error

**Hito 3 - Interfaz Backoffice** (COMPLETADO)
- Diseño previo de pantallas
- Vistas para gestionar todas las entidades
- Formularios con validacion

**Hito 4 - Front de venta (TPV)** (COMPLETADO)
- Vista principal con zonas y mesas (colores segun estado)
- Apertura de venta: seleccion de usuario, numero de comensales
- Gestion de venta: productos por familia, modificar cantidades, eliminar lineas
- Cierre de venta: usuario, calculo total, numero de ticket, liberacion de mesa

### Mejoras implementadas (Hito 6 - En proceso)

#### Mejoras funcionales (5 implementadas)

| Mejora | Estado | Descripcion |
|--------|--------|-------------|
| Roles de usuario | COMPLETADO | Administrador, Supervisor, Camarero y Chef con permisos diferenciados |
| Autenticacion por PIN | COMPLETADO | Solicitud de PIN en lugar de selector de usuario (diseño tactil) |
| Division de cuenta | COMPLETADO | Calculo automatico del importe por comensal y reflejo en ticket |
| Metodos de pago | COMPLETADO | Efectivo, tarjeta, pago mixto (varios metodos por venta) |
| Cierre de caja | COMPLETADO | Registro diario: total ventas y desglose por metodo de pago |

#### Mejoras tecnicas en curso (CQRS)

Se esta implementando **CQRS** (Command Query Responsibility Segregation) en los dominios del backend:

| Componente | Estado | Descripcion |
|------------|--------|-------------|
| Commands | EN PROCESO | Separacion de operaciones de escritura |
| Queries | EN PROCESO | Separacion de operaciones de lectura |
| Command Handlers | EN PROCESO | Manejo especifico para cada comando |
| Query Handlers | EN PROCESO | Manejo especifico para cada consulta |


**Estado por dominio:**

- **Familia**: Commands COMPLETADO, Queries COMPLETADO (Completado)
- **Producto**: Commands EN PROCESO, Queries PENDIENTE (En proceso)
- **Resto de dominios** (Usuario, Restaurante, Zona, Mesa, Impuesto, Pedido, Venta): Commands PENDIENTE, Queries PENDIENTE (Pendiente)
### Tecnologias utilizadas

| Capa | Tecnologia |
|------|------------|
| Backend | Laravel 12, PHP 8.x |
| Frontend | Angular 20, Ionic |
| Base de datos | MySQL |
| Contenedores | Docker + Docker Compose |
| Herramientas | Make, Git |

---

## Prerrequisitos

Para seguir esta guía necesitas tener instalado en tu máquina:

- **Docker** (y Docker Compose), para levantar la API, el frontend, la base de datos y DbGate. Sin Docker no podrás ejecutar `make start` ni el resto de comandos que dependen de los contenedores.
- **Make** (GNU Make), para usar los objetivos del `Makefile` (`make start`, `make install`, `make db-migrate`, etc.).
- **Git**, para clonar el repositorio.

---

## Cómo empezar

1. **Clonar el repositorio:**

   ```bash
   git clone <repo-url>
   cd 2026-training-laravel-angular
   ```

2. **Configurar entorno backend (solo la primera vez):** copiar el archivo de ejemplo:

   ```bash
   cp backend/.env.example backend/.env
   ```

3. **Levantar los contenedores Docker:**

   ```bash
   make start
   ```

4. **Instalar dependencias backend, migrar la base de datos y generar clave de aplicación:**

   ```bash
   make install   # composer install + migraciones (requiere que los contenedores estén levantados: make start)
   docker compose run --rm api php artisan key:generate
   ```

   Si el contenedor `api` no quedó en marcha, vuelve a levantar: `make start`.

5. **Frontend (Angular):** El repositorio ya incluye el proyecto en `frontend/`. Con `make start` el contenedor levanta la app automáticamente. Para desarrollo en primer plano con live reload: `make serve-frontend`.

Tras seguir estos pasos tendrás:

- **API (Laravel):** [http://localhost:8000](http://localhost:8000)
- **Frontend (Angular):** [http://localhost:4200](http://localhost:4200)
- **DbGate (MySQL):** [http://localhost:9051](http://localhost:9051) (conexión **Training MySQL** preconfigurada)

---

## Estructura del proyecto

### Backend (`backend/`)

El backend sigue un enfoque **DDD + Hexagonal**, con cada dominio encapsulado bajo su propio namespace.  
El ejemplo que se muestra a continuación es para el dominio `User`.

```text
App/
└── User/
    ├── Domain/
    │   ├── Entity/
    │   ├── ValueObject/
    │   └── Interfaces/
    ├── Application/
    │   └── CreateUser.php
    └── Infrastructure/
        ├── Persistence/
        └── Entrypoint/Http/
```

| Carpeta | Descripción |
|---------|-------------|
| **Domain/** | Lógica de negocio pura, entidades y value objects. |
| **Interfaces/** | Contratos del dominio (por ejemplo `UserRepositoryInterface`). |
| **Application/** | Casos de uso y handlers. |
| **Infrastructure/** | Adaptadores que conectan el dominio con el mundo externo: persistencia, HTTP, colas. |
| **Entrypoint/Http/** | Controladores o endpoints HTTP. |

### Frontend (`frontend/`)

Proyecto **Angular + Ionic** (standalone components). Cliente que consume la API del backend.

```text
frontend/src/app/
├── components/        # Componentes reutilizables
├── pages/             # Páginas de la aplicación
│   └── core/          # Páginas principales
│       └── home/
├── pipes/             # Pipes personalizados
├── providers/         # Interceptores y providers
│   └── interceptor.ts
└── services/          # Servicios (llamadas a la API, lógica compartida)
```

El interceptor HTTP (`providers/interceptor.ts`) prefija automáticamente la URL base de la API y añade los headers por defecto (`Accept`, `Accept-Language`).

### DbGate (cliente de base de datos)

Interfaz web para explorar y consultar la base MySQL. La conexión **Training MySQL** queda preconfigurada y apunta a la base `training` del servicio `db`.

---

## Objetivos de aprendizaje

- Comprender y aplicar **DDD**: separar Domain, Application e Infrastructure.
- Aprender a usar **repositorios e interfaces** para desacoplar dominio de la persistencia.
- Practicar la implementación de **casos de uso y handlers**.
- Exponer la lógica de negocio a través de **HTTP entrypoints** y mantener el dominio independiente del framework.
- Familiarizarse con **Docker**, **Composer** y **Node** en un flujo de desarrollo profesional.

---

## Buenas prácticas

- Programar contra **interfaces**, no implementaciones concretas.
- Evitar lógica de negocio en Controllers o Eloquent Models.
- Mantener los dominios **autocontenidos**, siguiendo la convención: `App/<Dominio>/{Domain, Application, Infrastructure}`.
- Escribir **tests** que dependan de la interfaz del dominio, no de la implementación concreta.

---

## Estilo de código

Para mantener un código consistente entre todos los colaboradores (humanos y IAs), se siguen estas pautas:

- **Backend (PHP):** PSR-12 y las recomendaciones de [Symfony Coding Standards](https://symfony.com/doc/current/contributing/code/standards.html).
- **Frontend (Angular):** [Angular Style Guide](https://angular.dev/style-guide).
- **Convenciones básicas**:
  - Una **clase por archivo**.
  - `camelCase` para variables y métodos, `PascalCase` para clases, `SCREAMING_SNAKE_CASE` para constantes.
  - Propiedades antes de los métodos; métodos públicos antes que protegidos y privados.
  - Imports (`use`) para todas las clases que no estén en el espacio de nombres actual.
- **Estructura y formato**:
  - Siempre usar paréntesis al instanciar clases (`new Foo()`).
  - En arrays multilínea, dejar **coma final** en cada elemento.
  - Añadir una línea en blanco antes de un `return` cuando mejore la legibilidad.
  - Evitar lógica compleja en una sola línea; preferir bloques claros con llaves siempre presentes.

Antes de subir cambios, se recomienda:

```bash
make test   # tests del backend (PHPUnit)
make lint   # formatear código PHP (Laravel Pint)
```
