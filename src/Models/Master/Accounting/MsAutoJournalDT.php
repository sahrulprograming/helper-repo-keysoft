<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class MsAutoJournalDT extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'ms_auto_journal_dt';
    protected $guarded = ['id'];
    public $timestamps = false;
}
