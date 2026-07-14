<?php

namespace App\Console\Commands;

use App\Models\DraftPlayer;
use App\Repositories\Applications\DraftPlayer\DraftPlayerRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneDraftPlayers extends Command
{
    protected $signature = 'players:prune-draft';

    protected $description = '清除超過 1 天未更新的玩家自助註冊草稿（連同其大頭照媒體）';

    public function handle(DraftPlayerRepository $repository): int
    {
        $deleted = 0;

        // DraftPlayer 已 implements HasMedia，delete() 時 Spatie 會自動連帶清除綁定的媒體與實體檔案；
        // 每筆草稿各自包一個交易，確保草稿列與其媒體列要嘛一起刪成功、要嘛都不刪，避免其中一步失敗留下孤兒資料。
        // 交易只保證 DB 列的原子性，不涵蓋實體檔案的刪除（那是交易外的副作用）。
        $repository->get(['updatedAtLt' => now()->subDay()])->each(function (DraftPlayer $draft) use (&$deleted): void {
            DB::transaction(function () use ($draft): void {
                $draft->delete();
            });

            ++$deleted;
        });

        $this->info("已清除 {$deleted} 筆逾期註冊草稿。");

        return self::SUCCESS;
    }
}
