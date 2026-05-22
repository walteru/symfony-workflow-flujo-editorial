<?php

namespace App\Command;

use App\Entity\Articulo;
use App\Repository\ArticuloRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:cargar-ejemplos', description: 'Carga artículos de ejemplo en distintos estados')]
class CargarEjemplosCommand extends Command
{
    public function __construct(private ArticuloRepository $articulos)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $datos = [
            ['Cómo empezar con Symfony', 'Borrador recién arrancado, todavía sin enviar a revisión.', 'Walter', 'borrador'],
            ['El componente Workflow explicado', 'Un artículo completo y listo, esperando que un editor lo revise para aprobarlo o pedir cambios.', 'Walter', 'en_revision'],
            ['Guards en la práctica', 'Ya revisado y aprobado por el equipo editorial; falta el último paso de publicarlo.', 'Walter', 'aprobado'],
        ];

        $ultimo = array_key_last($datos);
        foreach ($datos as $i => [$titulo, $contenido, $autor, $estado]) {
            $a = new Articulo();
            $a->setTitulo($titulo)->setContenido($contenido)->setAutor($autor)->setEstado($estado);
            // flush solo en el último: una sola escritura a la base.
            $this->articulos->save($a, $i === $ultimo);
        }

        $io->success(sprintf('%d artículos de ejemplo cargados.', count($datos)));

        return Command::SUCCESS;
    }
}
