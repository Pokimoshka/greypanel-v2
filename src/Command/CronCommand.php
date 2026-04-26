<?php

declare(strict_types=1);

namespace GreyPanel\Command;

use GreyPanel\Service\CronService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
    protected static $defaultName = 'cron:run';
    protected static $defaultDescription = 'Выполняет запланированные задачи (удаление просроченного VIP, обновление мониторинга)';

    private CronService $cronService;

    public function __construct(CronService $cronService)
    {
        parent::__construct();
        $this->cronService = $cronService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('[' . date('Y-m-d H:i:s') . '] Запуск cron задач...');
        $this->cronService->run();
        $output->writeln('[' . date('Y-m-d H:i:s') . '] Cron задачи выполнены.');
        return Command::SUCCESS;
    }
}
