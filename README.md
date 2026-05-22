# Flujo editorial con el componente Workflow de Symfony

Demo **autocontenida** (clone & run con Docker) del [componente Workflow](https://symfony.com/doc/current/workflow.html) de Symfony, aplicado a un caso real: el **flujo de publicación de un artículo** de un blog.

> 📝 **Post del blog:** [El componente Workflow de Symfony: máquinas de estado sin ensuciar el código](https://sincrodev.com/blog/symfony-workflow-maquina-estados-flujo-editorial/)

Un artículo recorre estos estados:

```
borrador ──enviar_a_revision──▶ en_revision ──aprobar──▶ aprobado ──publicar──▶ publicado
                                     │
                                     └──rechazar──▶ rechazado ──reescribir──▶ borrador
```

El objetivo no es el CRUD, sino mostrar **qué resuelve el Workflow** y dónde poner cada cosa:

- **El grafo** (estados + transiciones) vive en `config/packages/workflow.yaml`, declarativo.
- **Los guards** (reglas que vetan una transición) viven en un subscriber, no desparramados en `if`:
  - no se manda a revisión un artículo de menos de 50 caracteres;
  - solo un **editor** puede aprobar o rechazar (un autor no revisa lo suyo).
- **Los efectos secundarios** (al entrar a `publicado` se sella la fecha de publicación) van en un evento `entered`.
- La UI solo ofrece las transiciones realmente posibles, porque pregunta `getEnabledTransitions()` (que ya respeta los guards).

> Para ver el guard de rol en acción, usá el enlace **"ver como editor / ver como autor"** arriba a la derecha. Simula el rol con la sesión para no montar todo el sistema de login.

## Requisitos

Solo **Docker** y **Docker Compose**. No necesitás PHP ni Composer en tu máquina.

## Cómo correrlo

```bash
make start      # construye y levanta el contenedor (http://localhost:8092)
make migrate    # crea la base SQLite y el esquema
make fixtures   # carga 3 artículos de ejemplo en distintos estados
```

Abrí <http://localhost:8092>.

Otros comandos útiles (`make help` los lista todos):

```bash
make test       # corre los tests (guards y evento de publicación)
make sh         # shell dentro del contenedor
make console c="debug:workflow publicacion"   # inspecciona el grafo
make down       # baja todo
```

## Cómo está armado

| Pieza | Archivo |
|---|---|
| Definición del workflow | `src/config/packages/workflow.yaml` |
| Entidad y su estado | `src/src/Entity/Articulo.php` (propiedad `estado`) |
| Guards + evento `entered` | `src/src/Workflow/PublicacionSubscriber.php` |
| Rol simulado (autor/editor) | `src/src/Workflow/RolActual.php` |
| Controlador (aplica transiciones) | `src/src/Controller/ArticuloController.php` |
| Tests | `src/tests/Workflow/PublicacionSubscriberTest.php` |

## Stack

- Symfony 6.4 (PHP 8.3) · Doctrine ORM con **SQLite** (sin servicio de base aparte)
- Twig para las vistas · Apache en el contenedor

## La serie Fast Track

Este es el **#1** de una serie que desentraña secciones de *[Symfony: The Fast Track](https://symfony.com/doc/6.4/the-fast-track/en/index.html)* con casos de uso reales. Los tres componen sobre el mismo evento `entered.publicado` del Workflow:

- **#1 Workflow** — estados, guards y eventos · [repo](https://github.com/walteru/symfony-workflow-flujo-editorial) · [post](https://sincrodev.com/blog/symfony-workflow-maquina-estados-flujo-editorial/)
- **#2 Messenger** — trabajo lento fuera del request · [repo](https://github.com/walteru/symfony-messenger-tareas-async) · [post](https://sincrodev.com/blog/symfony-messenger-tareas-async-fuera-del-request/)
- **#3 HttpCache + ESI** — páginas públicas rápidas · [repo](https://github.com/walteru/symfony-cache-paginas-publicas) · [post](https://sincrodev.com/blog/symfony-cache-paginas-publicas-rapidas-sin-servir-viejo/)

## Licencia

MIT — ver [LICENSE](LICENSE).
