<?php

namespace App\Core\Contracts;

interface ProveedorOcr
{
    /**
     * @param string $rutaImagen Ruta local de la imagen a procesar.
     * @return array{datos: array<string,mixed>, confianza: array<string,float>|null}
     */
    public function extraer(string $rutaImagen): array;
}
