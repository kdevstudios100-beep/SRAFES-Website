<?php

namespace App\Support\Academics;

/**
 * The roll number already belongs to another active student in the same
 * session, class and section. The message is safe to show to staff.
 */
class RollNumberTakenException extends EnrollmentException {}
