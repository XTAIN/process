<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use XTAIN\Process\Builder;

abstract class AbstractBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function assertBuilder($builder)
    {
        /** @var $builder Builder */
        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertInstanceOf(Process::class, $builder->getProcess());
        $this->assertInstanceOf(ProcessBuilder::class, $builder->getProcessBuilder());
    }
}