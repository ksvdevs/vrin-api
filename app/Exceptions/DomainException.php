<?php

namespace App\Exceptions;

use Exception;

/**
 * Regla de negocio violada (transición de estado inválida, precondición
 * incumplida, fase no habilitada). Se renderiza como HTTP 409 JSON.
 */
class DomainException extends Exception {}
