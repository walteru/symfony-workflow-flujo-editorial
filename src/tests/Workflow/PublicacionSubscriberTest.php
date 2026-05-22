<?php

namespace App\Tests\Workflow;

use App\Entity\Articulo;
use App\Workflow\PublicacionSubscriber;
use App\Workflow\RolActual;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class PublicacionSubscriberTest extends TestCase
{
    private function rol(string $valor): RolActual
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $stack = new RequestStack();
        $stack->push($request);
        $rol = new RolActual($stack);
        $rol->set($valor);

        return $rol;
    }

    private function guard(Articulo $articulo, string $transicion, string $from, string $to): GuardEvent
    {
        return new GuardEvent($articulo, new Marking([$from => 1]), new Transition($transicion, $from, $to));
    }

    public function testNoSeManiaARevisionUnArticuloCorto(): void
    {
        $sub = new PublicacionSubscriber($this->rol(RolActual::AUTOR));
        $articulo = (new Articulo())->setContenido('muy corto');

        $event = $this->guard($articulo, 'enviar_a_revision', 'borrador', 'en_revision');
        $sub->guardEnviarARevision($event);

        $this->assertTrue($event->isBlocked(), 'Un artículo de menos de 50 caracteres no debería pasar a revisión');
    }

    public function testArticuloLargoSiPasaARevision(): void
    {
        $sub = new PublicacionSubscriber($this->rol(RolActual::AUTOR));
        $articulo = (new Articulo())->setContenido(str_repeat('contenido suficiente ', 5));

        $event = $this->guard($articulo, 'enviar_a_revision', 'borrador', 'en_revision');
        $sub->guardEnviarARevision($event);

        $this->assertFalse($event->isBlocked());
    }

    public function testUnAutorNoPuedeAprobar(): void
    {
        $sub = new PublicacionSubscriber($this->rol(RolActual::AUTOR));
        $event = $this->guard(new Articulo(), 'aprobar', 'en_revision', 'aprobado');
        $sub->guardSoloEditor($event);

        $this->assertTrue($event->isBlocked());
    }

    public function testUnEditorSiPuedeAprobar(): void
    {
        $sub = new PublicacionSubscriber($this->rol(RolActual::EDITOR));
        $event = $this->guard(new Articulo(), 'aprobar', 'en_revision', 'aprobado');
        $sub->guardSoloEditor($event);

        $this->assertFalse($event->isBlocked());
    }

    public function testAlPublicarSeSellaLaFecha(): void
    {
        $sub = new PublicacionSubscriber($this->rol(RolActual::EDITOR));
        $articulo = new Articulo();
        $this->assertNull($articulo->getPublicadoEl());

        $event = new EnteredEvent($articulo, new Marking(['publicado' => 1]), new Transition('publicar', 'aprobado', 'publicado'));
        $sub->alPublicar($event);

        $this->assertNotNull($articulo->getPublicadoEl());
    }
}
