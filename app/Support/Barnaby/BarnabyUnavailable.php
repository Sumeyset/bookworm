<?php

namespace App\Support\Barnaby;

use RuntimeException;

/** No provider could answer (no keys set, quota used up, or the services are down). */
class BarnabyUnavailable extends RuntimeException
{
}
