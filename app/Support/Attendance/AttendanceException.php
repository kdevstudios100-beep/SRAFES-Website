<?php

namespace App\Support\Attendance;

use RuntimeException;

/**
 * Attendance that was refused; the message is safe to show to staff.
 */
class AttendanceException extends RuntimeException {}
