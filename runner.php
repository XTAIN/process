<?php

$fork = function_exists('pcntl_fork') && function_exists('posix_setsid');

function mainthead($autoload, $dto) {
    require_once $autoload;

    /** @var \XTAIN\Process\Daemon\Data $dto */
    $dto = unserialize($dto);

    $logger = $dto->getLogger();
    $process = $dto->getProcess();

    if ($logger !== null) {
        $process->run(function ($out, $data) use($logger) {
            if ($out === \Symfony\Component\Process\Process::OUT) {
                $logger->info($data, ['pid' => getmypid()]);
            } else {
                $logger->warning($data, ['pid' => getmypid()]);
            }
        });
    } else {
        $process->run();
    }

    $process->wait();
}

if ($fork && count($_SERVER['argv']) == 1) {
    $autoload = trim(fgets(STDIN));
    $dto = stream_get_contents(STDIN);

    switch ($pid = pcntl_fork()) {
        case -1:
            echo "Could not fork";
            exit(1);
        case 0: // this is the child process
            break;
        default: // otherwise this is the parent process
            echo $pid;
            exit;
    }

    if (posix_setsid() === -1) {
        exit('could not setsid');
    }

    fclose(STDIN);
    fclose(STDOUT);
    fclose(STDERR);

    pcntl_signal(SIGTSTP, SIG_IGN);
    pcntl_signal(SIGTTOU, SIG_IGN);
    pcntl_signal(SIGTTIN, SIG_IGN);
    pcntl_signal(SIGHUP, SIG_IGN);

    mainthead($autoload, $dto);
} else {
    $stdin = $_SERVER['argv'][1];
    $stdout = $_SERVER['argv'][2];

    $stdin = fopen($stdin, 'r');
    $autoload = trim(fgets($stdin));
    $dto = stream_get_contents($stdin);
    fclose($stdin);

    $fo = fopen($stdout, 'w');
    if (flock($fo, LOCK_EX)) {
        fwrite($fo, getmypid());
        flock($fo, LOCK_UN);
        fclose($fo);
    }

    mainthead($autoload, $dto);
}