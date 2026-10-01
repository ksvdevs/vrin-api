<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Violación de una regla del dominio (p. ej. transición de estado inválida).
 * Se mapea a HTTP 409 en bootstrap/app.php.
 */
class DomainException extends RuntimeException {}
