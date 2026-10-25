<?php

namespace App\Support\Exams;

use RuntimeException;

/**
 * An exam action that was refused; the message is safe to show to staff.
 */
class ExamException extends RuntimeException {}
