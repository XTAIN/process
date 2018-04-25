<?php

exit(function_exists('pcntl_fork') && function_exists('posix_setsid') ? 0 : 1);