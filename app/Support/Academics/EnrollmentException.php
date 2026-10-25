<?php

namespace App\Support\Academics;

use RuntimeException;

/**
 * A placement change that was refused; the message is safe to show to staff.
 */
class EnrollmentException extends RuntimeException {}
