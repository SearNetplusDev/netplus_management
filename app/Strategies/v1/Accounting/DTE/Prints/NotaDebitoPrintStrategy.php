<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Models\Accounting\DTEModel;
use App\Strategies\v1\Accounting\DTE\Prints\BasePrint;
use Barryvdh\DomPDF\PDF as DomPDF;

readonly class NotaDebitoPrintStrategy extends BasePrint
{

    protected function generate(DTEModel $model): DomPDF
    {
        // TODO: Implement generate() method.
    }
}
